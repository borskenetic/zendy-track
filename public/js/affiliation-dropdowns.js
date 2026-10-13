(function (window) {
    'use strict';

    function fillSelect(select, options, selected, placeholder) {
        if (!select) {
            return;
        }

        var previous = selected != null ? selected : (select.value || '');
        select.innerHTML = '';

        var blank = document.createElement('option');
        blank.value = '';
        blank.disabled = true;
        blank.textContent = placeholder || 'Select';
        select.appendChild(blank);

        var matched = false;
        (options || []).forEach(function (value) {
            var option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            if (value === previous) {
                option.selected = true;
                matched = true;
            }
            select.appendChild(option);
        });

        if (!matched) {
            blank.selected = true;
            select.value = '';
        }
    }

    function initAffiliationDropdowns(config) {
        var catalog = config.catalog || {};
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

        function syncYearLevels(preferredYear, preferredCourse) {
            var campus = campusSelect.value;
            var yearLevels = campus && catalog[campus] ? Object.keys(catalog[campus]) : [];
            fillSelect(yearSelect, yearLevels, preferredYear || '', 'Select year level');
            syncCourses(preferredCourse || '');
        }

        function syncCourses(preferredCourse) {
            var campus = campusSelect.value;
            var year = yearSelect.value;
            var courses = campus && year && catalog[campus] && catalog[campus][year]
                ? catalog[campus][year]
                : [];
            fillSelect(courseSelect, courses, preferredCourse || '', 'Select course / track');
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

        if (!campusSelect.dataset.affiliationBound) {
            campusSelect.addEventListener('change', function () {
                syncYearLevels('', '');
            });
            yearSelect.addEventListener('change', function () {
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
    }

    window.initAffiliationDropdowns = initAffiliationDropdowns;
})(window);
