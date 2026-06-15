"use strict";

// Class definition
var KTKebunTable = function () {
    // Shared variables
    var table;
    var datatable;

    // Private functions
    var initDatatable = function () {
        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
            'columnDefs': [
                { orderable: false, targets: 4 }, // Disable ordering on column 4 (actions)
            ]
        });
    }

    // Search Datatable
    var handleSearchDatatable = () => {
        const filterSearch = document.querySelector('[data-kt-kebun-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_table_kebuns');

            if (!table) {
                return;
            }

            initDatatable();
            handleSearchDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTKebunTable.init();
});
