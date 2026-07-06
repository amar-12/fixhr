
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="https://www.unpkg.com/datatable-customizer/index.js"></script>

<script src="{{ asset('assets/plugins/formwizard/jquery.smartWizard.js?v1.3') }}"></script>
<script src="{{ asset('assets/plugins/formwizard/fromwizard.js?v3.80') }}"></script>
<script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
<script src="{{ asset('assets/js/filupload.js?v=10') }}"></script>


{{-- external link --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JQUERY JS -->


<!-- BOOTSTRAP JS -->
<script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>


<!-- INTERNAL MULTIPLE SELECT JS -->
<script src="{{ asset('assets/plugins/multipleselect/multiple-select.js') }}" wire:ignore></script>
<script src="{{ asset('assets/plugins/multipleselect/multi-select.js') }}" wire:ignore></script>

<!-- MOMENT JS -->
<script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>


<!--SIDEMENU JS -->
<script src="{{ asset('assets/plugins/sidemenu/sidemenu.js') }}"></script>

<!-- SELECT2 JS -->
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.js') }}"></script>

<!-- INTERNAL DATA TABLES -->
<script src="{{ asset('assets/js/datatables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/responsive.bootstrap5.min.js') }}"></script>

<!-- STICKY JS -->
<script src="{{ asset('assets/js/sticky.js') }}"></script>

<!-- CUSTOM JS -->
<script src="{{ asset('assets/js/custom.js?v=0.6') }}"></script>


<script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.min.js"></script>
<script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.js"></script>

<script>
    $('.search_test').SumoSelect({
        search: true,
        searchText: 'Search'
        // triggerChangeCombined: true,

    });

    $('.sumo_search').SumoSelect({
        search: true,
        searchText: 'Search'
        // triggerChangeCombined: true,
    });
</script>
