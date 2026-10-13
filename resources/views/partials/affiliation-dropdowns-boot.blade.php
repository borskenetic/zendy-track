{{-- Campus → year level → course dropdowns (inlined so deploy can't miss the asset) --}}
<script>
window.AFFILIATION_CATALOG = @json(\App\Models\User::affiliationCatalog());
window.AFFILIATION_ALL_YEARS = @json(\App\Models\User::gradeOptions());
window.AFFILIATION_ALL_COURSES = @json(\App\Models\User::courseOptions());

(function (window) {
    'use strict';

    function uniqueSorted(values) {
        var seen = {};
        var out = [];
        (values || []).forEach(function (value) {
            if (!seen[value]) {
                seen[value] = true;
                out.push(value);
            }
        });
        out.sort();
        return out;
    }

    function yearLevelsFor(catalog, campus) {
        if (!campus || !catalog || !catalog[campus]) {
            return window.AFFILIATION_ALL_YEARS || [];
        }
        return Object.keys(catalog[campus]);
    }

    function coursesFor(catalog, campus, year) {
        if (campus && year && catalog[campus] && catalog[campus][year]) {
            return Array.isArray(catalog[campus][year]) ? catalog[campus][year] : [];
        }

        if (campus && catalog[campus]) {
            var campusCourses = [];
            Object.keys(catalog[campus]).forEach(function (level) {
                (catalog[campus][level] || []).forEach(function (course) {
                    campusCourses.push(course);
                });
            });
            return uniqueSorted(campusCourses);
        }

        if (year) {
            var yearCourses = [];
            Object.keys(catalog || {}).forEach(function (campusName) {
                (catalog[campusName][year] || []).forEach(function (course) {
                    yearCourses.push(course);
                });
            });
            return uniqueSorted(yearCourses);
        }

        return window.AFFILIATION_ALL_COURSES || [];
    }

    function fillSelect(select, options, selected, placeholder, extraOptions, autoSelectSingle) {
        if (!select) {
            return;
        }

        var list = Array.isArray(options) ? options : [];
        var previous = selected == null ? '' : String(selected);
        var extras = Array.isArray(extraOptions) ? extraOptions : [];

        select.innerHTML = '';

        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = placeholder || 'Select';
        if (placeholderIsDisabled(placeholder)) {
            blank.disabled = true;
        }
        select.appendChild(blank);

        var matched = false;

        extras.forEach(function (extra) {
            var option = document.createElement('option');
            option.value = extra.value;
            option.textContent = extra.label;
            if (String(extra.value) === previous) {
                option.selected = true;
                matched = true;
            }
            select.appendChild(option);
        });

        for (var i = 0; i < list.length; i++) {
            var value = list[i];
            var option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            if (value === previous) {
                option.selected = true;
                matched = true;
            }
            select.appendChild(option);
        }

        if (!matched && autoSelectSingle && list.length === 1 && extras.length === 0 && previous === '') {
            select.value = list[0];
            matched = true;
        }

        if (!matched) {
            blank.selected = true;
            select.value = '';
        }
    }

    function placeholderIsDisabled(placeholder) {
        return !placeholder || placeholder.indexOf('All ') !== 0;
    }

    // Form mode: required student fields, auto-select single year level.
    window.initAffiliationDropdowns = function (config) {
        var catalog = config.catalog || window.AFFILIATION_CATALOG || {};
        var campusSelect = document.getElementById(config.campusId || 'campus');
        var yearSelect = document.getElementById(config.yearId || 'grade');
        var courseSelect = document.getElementById(config.courseId || 'course');
        var studentOnlyIds = config.studentOnlyIds || [];
        var isStudentFn = config.isStudent || function () { return true; };

        if (!campusSelect || !yearSelect || !courseSelect) {
            return { refresh: function () {} };
        }

        function setStudentVisibility() {
            var show = isStudentFn();
            studentOnlyIds.forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) {
                    return;
                }
                el.classList.toggle('hidden', !show);
                el.style.display = show ? '' : 'none';
            });
            yearSelect.required = show;
            courseSelect.required = show;
            if (!show) {
                yearSelect.value = '';
                courseSelect.value = '';
            }
        }

        function syncCourses(preferredCourse) {
            var campus = campusSelect.value;
            var year = yearSelect.value;
            var courses = campus && year ? coursesFor(catalog, campus, year) : [];
            fillSelect(courseSelect, courses, preferredCourse || '', 'Select course / track', [], true);
        }

        function syncYearLevels(preferredYear, preferredCourse) {
            var yearLevels = campusSelect.value ? yearLevelsFor(catalog, campusSelect.value) : [];
            fillSelect(yearSelect, yearLevels, preferredYear || '', 'Select year level', [], true);
            syncCourses(preferredCourse || '');
        }

        function refresh(opts) {
            opts = opts || {};
            setStudentVisibility();
            if (isStudentFn()) {
                syncYearLevels(
                    opts.year != null ? opts.year : yearSelect.value,
                    opts.course != null ? opts.course : courseSelect.value
                );
            } else {
                fillSelect(yearSelect, [], '', 'Select year level', [], false);
                fillSelect(courseSelect, [], '', 'Select course / track', [], false);
            }
        }

        if (!campusSelect.dataset.affiliationBound) {
            campusSelect.addEventListener('change', function () {
                if (!isStudentFn()) {
                    return;
                }
                syncYearLevels('', '');
            });
            yearSelect.addEventListener('change', function () {
                if (!isStudentFn()) {
                    return;
                }
                syncCourses('');
            });
            campusSelect.dataset.affiliationBound = '1';
        }

        if (config.roleSelectId) {
            var roleSelect = document.getElementById(config.roleSelectId);
            if (roleSelect && !roleSelect.dataset.affiliationBound) {
                roleSelect.addEventListener('change', function () {
                    refresh();
                });
                roleSelect.dataset.affiliationBound = '1';
            }
        }

        if (config.initialCampus) {
            campusSelect.value = config.initialCampus;
        }

        refresh({
            year: config.initialYear || '',
            course: config.initialCourse || '',
        });

        return { refresh: refresh };
    };

    // Filter mode: optional fields with "All ..." placeholders; keep special options like __none__.
    window.initAffiliationFilters = function (config) {
        var catalog = config.catalog || window.AFFILIATION_CATALOG || {};
        var campusSelect = document.getElementById(config.campusId || 'filter_campus');
        var yearSelect = document.getElementById(config.yearId || 'filter_grade');
        var courseSelect = document.getElementById(config.courseId || 'filter_course');
        var yearExtras = config.yearExtras || [];
        var courseExtras = config.courseExtras || [];

        if (!campusSelect || !yearSelect || !courseSelect) {
            return { refresh: function () {} };
        }

        function syncCourses(preferredCourse) {
            fillSelect(
                courseSelect,
                coursesFor(catalog, campusSelect.value, yearSelect.value),
                preferredCourse || '',
                config.coursePlaceholder || 'All courses',
                courseExtras,
                false
            );
        }

        function syncYearLevels(preferredYear, preferredCourse) {
            fillSelect(
                yearSelect,
                yearLevelsFor(catalog, campusSelect.value),
                preferredYear || '',
                config.yearPlaceholder || 'All year levels',
                yearExtras,
                false
            );
            syncCourses(preferredCourse || '');
        }

        function refresh(opts) {
            opts = opts || {};
            syncYearLevels(
                opts.year != null ? opts.year : yearSelect.value,
                opts.course != null ? opts.course : courseSelect.value
            );
        }

        if (!campusSelect.dataset.affiliationFilterBound) {
            campusSelect.addEventListener('change', function () {
                syncYearLevels('', '');
            });
            yearSelect.addEventListener('change', function () {
                syncCourses('');
            });
            campusSelect.dataset.affiliationFilterBound = '1';
        }

        if (config.initialCampus) {
            campusSelect.value = config.initialCampus;
        }

        refresh({
            year: config.initialYear || '',
            course: config.initialCourse || '',
        });

        return { refresh: refresh };
    };
})(window);
</script>
