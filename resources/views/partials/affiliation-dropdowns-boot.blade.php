{{-- Campus → year level → course dropdowns (inlined so deploy can't miss the asset) --}}
<script>
window.AFFILIATION_CATALOG = @json(\App\Models\User::affiliationCatalog());

(function (window) {
    'use strict';

    if (typeof window.initAffiliationDropdowns === 'function') {
        return;
    }

    function fillSelect(select, options, selected, placeholder) {
        if (!select) {
            return;
        }

        var list = Array.isArray(options) ? options : [];
        var previous = selected == null ? '' : String(selected);

        select.innerHTML = '';

        var blank = document.createElement('option');
        blank.value = '';
        blank.disabled = true;
        blank.textContent = placeholder || 'Select';
        select.appendChild(blank);

        var matched = false;
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

        // If only one real option, select it automatically.
        if (!matched && list.length === 1) {
            select.value = list[0];
            matched = true;
        }

        if (!matched) {
            blank.selected = true;
            select.value = '';
        }
    }

    function yearLevelsFor(catalog, campus) {
        if (!campus || !catalog || !catalog[campus]) {
            return [];
        }
        return Object.keys(catalog[campus]);
    }

    function coursesFor(catalog, campus, year) {
        if (!campus || !year || !catalog || !catalog[campus] || !catalog[campus][year]) {
            return [];
        }
        var list = catalog[campus][year];
        return Array.isArray(list) ? list : [];
    }

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
            var courses = coursesFor(catalog, campusSelect.value, yearSelect.value);
            fillSelect(courseSelect, courses, preferredCourse || '', 'Select course / track');
        }

        function syncYearLevels(preferredYear, preferredCourse) {
            var yearLevels = yearLevelsFor(catalog, campusSelect.value);
            fillSelect(yearSelect, yearLevels, preferredYear || '', 'Select year level');
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
                fillSelect(yearSelect, [], '', 'Select year level');
                fillSelect(courseSelect, [], '', 'Select course / track');
            }
        }

        function onCampusChange() {
            if (!isStudentFn()) {
                return;
            }
            syncYearLevels('', '');
        }

        function onYearChange() {
            if (!isStudentFn()) {
                return;
            }
            syncCourses('');
        }

        if (!campusSelect.dataset.affiliationBound) {
            campusSelect.addEventListener('change', onCampusChange);
            yearSelect.addEventListener('change', onYearChange);
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
})(window);
</script>
