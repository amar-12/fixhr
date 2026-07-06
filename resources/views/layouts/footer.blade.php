 <!-- Start Footer Style With Black Color Area -->
 <div class="footer-area-style-with-black-color">
     <div class="container">
     <div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="single-footer-widget">
            <a href="index-2.html" class="logo">
                <img src="{{ asset('frontend/assets/images/logo/logo_dark.png') }}"
                     style="height:30px;width:100px" alt="logo">
            </a>
            <p>Fix HR is the most unique mobile and web based software, designed for managing attendance
                records of startups, small businesses, and supporting modern companies.</p>
            <ul class="social-links">
                <li><a href="https://www.facebook.com/" target="_blank"><i class="ri-facebook-fill"></i></a></li>
                <li><a href="https://www.twitter.com/" target="_blank"><i class="ri-twitter-fill"></i></a></li>
                <li><a href="https://www.linkedin.com/" target="_blank"><i class="ri-linkedin-fill"></i></a></li>
                <li><a href="https://www.messenger.com/" target="_blank"><i class="ri-messenger-fill"></i></a></li>
                <li><a href="https://www.github.com/" target="_blank"><i class="ri-github-fill"></i></a></li>
            </ul>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-4 col-4">
        <div class="single-footer-widget pl-2">
            <h3>Company</h3>
            <ul class="links-list">
                <li><a href="#">About Us</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Refund Policy</a></li>
                <li><a href="#faq">FAQ's</a></li>
                <li><a href="#trusted-user">Reviews</a></li>
            </ul>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-4 col-4">
        <div class="single-footer-widget">
            <h3>Support</h3>
            <ul class="links-list">
                <li><a href="#">Services</a></li>
                <li><a href="#">Support</a></li>
                <li><a href="{{url('/privacy-policy')}}">Privacy Policy</a></li>
                <li><a href="#faq">FAQ's</a></li>
                <li><a href="#contact-us">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-4 col-4">
        <div class="single-footer-widget">
            <h3>Useful Links</h3>
            <ul class="links-list">
                <li><a href="{{url('/privacy-policy')}}">Privacy Policy</a></li>
                <li><a href="#">Return Policy</a></li>
                <li><a href="#">Terms &amp; Conditions</a></li>
                <li><a href="#">How It Works?</a></li>
            </ul>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="single-footer-widget">
            <h3>Newsletter</h3>
            <p>If you want to receive monthly updates from us just pop your email in the box.</p>
            <form class="newsletter-form" data-toggle="validator">
                <input type="text" class="input-newsletter" placeholder="Your Email" name="EMAIL" required=""
                       autocomplete="off">
                <button type="submit"><i class="ri-send-plane-2-line"></i></button>
                <div id="validator-newsletter" class="form-result"></div>
            </form>
        </div>
    </div>
</div>

         <div class="copyright-area">
             <div class="col-lg-11 col-md-11 text-center text-light">
                 <span><span><a style="color:#1877f2" href="{{url('/privacy-policy')}}" target="blank">Privacy
                             Policy</a></span> | <span><a style="color:#1877f2" href="{{url('/terms-and-conditions')}}"
                             target="blank">Terms & Conditions</a></span> | <span><a style="color:#1877f2"
                             href="{{url('/refund-policy')}}" target="blank">Refund Policy</a></span></span>
             </div>
             <div class="col-lg-11 col-md-11 text-center ">
                 <p class="text-light"><b>&copy; 2024 Fix HR | All right reserved | Designed by <a
                             style="color: #1877f2; font-width:bold; font-size:16px" href="https://fixingdots.com/"
                             target="blank">FixingDots</a>.</b> | <strong>Version:</strong> 1.0.1</p>
             </div>
         </div>
     </div>

     <div class="footer-white-shape">
         <img src="{{asset('frontend/assets/images/footer-shape.png')}}" alt="image">
     </div>
 </div>
