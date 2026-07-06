
<footer class="footer d-flex">
    {{-- <div id="curved-corner-bottomleft"></div> --}}
    <div class="container">
        <div class="row align-items-center flex-row-reverse">
           <!--  <div class="col-md-12 col-sm-12 mt-3 mt-lg-0 text-center"><b>Copyright © {{date('Y')}} | All Rights Reserved | Designed with <span
                    class="fa fa-heart text-danger"></span> by <a class="text-primary" href="https://fixingdots.com/" target="blank"><b>Fixing Dots</b></a></b>
            </div> -->
            <div class="col-md-12 col-sm-12 mt-3 mt-lg-0 text-center"><b>Copyright © {{date('Y')}} | All Rights Reserved | Designed by <b>
               <a  href="https://fixingdots.com/" ><img  src="https://fixhr.app/assets/logo/FD New logo 150x38.png" alt="img"></a> </b>| <strong>Version:</strong>   v{{ config('app.version') }} </b>
            </div>
        </div>
    </div>
</footer>

<!-- CHANGE PASSWORD MODAL -->
<div class="modal fade"  id="changepasswordnmodal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button  class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" placeholder="password" value="">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" placeholder="password" value="">
                </div>
            </div>
            <div class="modal-footer">
                <a  href="javascript:void(0);" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</a>
                <a  href="javascript:void(0);" class="btn btn-outline-primary">Confirm</a>
            </div>
        </div>
    </div>
</div>
<!-- END CHANGE PASSWORD MODAL  -->


<!-- CLOCK-IN MODAL -->
<div class="modal fade"  id="clockinmodal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span class="feather feather-clock  me-1"></span>Clock In</h5>
                <button  class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="countdowntimer"><span id="clocktimer" class="border-0"></span></div>
                <div class="form-group">
                    <label class="form-label">Note:</label>
                    <textarea class="form-control" rows="3">Some text here...</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button  class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
                <button  class="btn btn-outline-primary">Clock In</button>
            </div>
        </div>
    </div>
</div>
<!-- END CLOCK-IN MODAL -->
