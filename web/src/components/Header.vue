<template>
  <!-- ==========Preloader========== -->
  <div class="preloader">
    <div class="preloader-inner">
      <div class="preloader-icon">
        <span></span>
        <span></span>
      </div>
    </div>
  </div>
  <!-- ==========Preloader========== -->
  <!-- ==========Overlay========== -->
  <div class="overlay"></div>
  <a href="#0" class="scrollToTop">
    <i class="fas fa-angle-up"></i>
  </a>
  <!-- ==========Overlay========== -->

  <!-- ==========Header-Section========== -->
  <header class="header-section">
    <div class="container">
      <div class="header-wrapper">
        <div class="logo">
          <router-link to="/">
            <img src="/assets/images/logo/logo.png" alt="logo">
          </router-link>
        </div>
        <ul class="menu">
          <li>
            <a href="#0" class="active">Home</a>
            <ul class="submenu">
              <li>
                <a href="index.html">Home One</a>
              </li>
              <li>
                <a href="#0" class="active">Home Two</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="#0">movies</a>
            <ul class="submenu">
              <li>
                <a href="movie-grid.html">Movie Grid</a>
              </li>
              <li>
                <a href="movie-list.html">Movie List</a>
              </li>
              <li>
                <a href="movie-details.html">Movie Details</a>
              </li>
              <li>
                <a href="movie-details-2.html">Movie Details 2</a>
              </li>
              <li>
                <a href="movie-ticket-plan.html">Movie Ticket Plan</a>
              </li>
              <li>
                <a href="movie-seat-plan.html">Movie Seat Plan</a>
              </li>
              <li>
                <a href="movie-checkout.html">Movie Checkout</a>
              </li>
              <li>
                <a href="popcorn.html">Movie Food</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="#0">events</a>
            <ul class="submenu">
              <li>
                <a href="events.html">Events</a>
              </li>
              <li>
                <a href="event-details.html">Event Details</a>
              </li>
              <li>
                <a href="event-speaker.html">Event Speaker</a>
              </li>
              <li>
                <a href="event-ticket.html">Event Ticket</a>
              </li>
              <li>
                <a href="event-checkout.html">Event Checkout</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="#0">sports</a>
            <ul class="submenu">
              <li>
                <a href="sports.html">Sports</a>
              </li>
              <li>
                <a href="sport-details.html">Sport Details</a>
              </li>
              <li>
                <a href="sports-ticket.html">Sport Ticket</a>
              </li>
              <li>
                <a href="sports-checkout.html">Sport Checkout</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="#0">pages</a>
            <ul class="submenu">
              <li>
                <a href="about.html">About Us</a>
              </li>
              <li>
                <a href="apps-download.html">Apps Download</a>
              </li>
              <li>
                <a href="sign-in.html">Sign In</a>
              </li>
              <li>
                <a href="sign-up.html">Sign Up</a>
              </li>
              <li>
                <a href="404.html">404</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="#0">blog</a>
            <ul class="submenu">
              <li>
                <a href="blog.html">Blog</a>
              </li>
              <li>
                <a href="blog-details.html">Blog Single</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="contact.html">contact</a>
          </li>
          <li class="header-button pr-0">
            <a href="sign-up.html">join us</a>
          </li>
        </ul>
        <div class="header-bar d-lg-none">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
  </header>
  <!-- ==========Header-Section========== -->
  <!-- ==========Banner-Section========== -->
  <!--  <section class="banner-section">-->
  <!--    <div class="banner-bg bg_img bg-fixed" data-background="/assets/images/banner/banner01.jpg"></div>-->
  <!--    <div class="container">-->
  <!--      <div class="banner-content">-->
  <!--        <h1 class="title  cd-headline clip"><span class="d-block">book your</span> tickets for-->
  <!--          <span class="color-theme cd-words-wrapper p-0 m-0">-->
  <!--                        <b class="is-visible">Movie</b>-->
  <!--                        <b>Event</b>-->
  <!--                        <b>Sport</b>-->
  <!--                    </span>-->
  <!--        </h1>-->
  <!--        <p>Safe, secure, reliable ticketing.Your ticket to live entertainment!</p>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--  </section>-->
  <!-- ==========Banner-Section========== -->
  <!-- ==========Ticket-Search========== -->
  <!--  <section class="search-ticket-section padding-top pt-lg-0">-->
  <!--    <div class="container">-->
  <!--      <div class="search-tab bg_img" data-background="/assets/images/ticket/ticket-bg01.jpg">-->
  <!--        <div class="row align-items-center mb&#45;&#45;20">-->
  <!--          <div class="col-lg-6 mb-20">-->
  <!--            <div class="search-ticket-header">-->
  <!--              <h6 class="category">welcome to Boleto </h6>-->
  <!--              <h3 class="title">what are you looking for</h3>-->
  <!--            </div>-->
  <!--          </div>-->
  <!--          <div class="col-lg-6 mb-20">-->
  <!--            <ul class="tab-menu ticket-tab-menu">-->
  <!--              <li class="active">-->
  <!--                <div class="tab-thumb">-->
  <!--                  <img src="/assets/images/ticket/ticket-tab01.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span>movie</span>-->
  <!--              </li>-->
  <!--              <li>-->
  <!--                <div class="tab-thumb">-->
  <!--                  <img src="/assets/images/ticket/ticket-tab02.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span>events</span>-->
  <!--              </li>-->
  <!--              <li>-->
  <!--                <div class="tab-thumb">-->
  <!--                  <img src="/assets/images/ticket/ticket-tab03.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span>sports</span>-->
  <!--              </li>-->
  <!--            </ul>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--        <div class="tab-area">-->
  <!--          <div class="tab-item active">-->
  <!--            <form class="ticket-search-form">-->
  <!--              <div class="form-group large">-->
  <!--                <input type="text" placeholder="Search fo Movies">-->
  <!--                <button type="submit"><i class="fas fa-search"></i></button>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/city.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">city</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="london">London</option>-->
  <!--                  <option value="dhaka">dhaka</option>-->
  <!--                  <option value="rosario">rosario</option>-->
  <!--                  <option value="madrid">madrid</option>-->
  <!--                  <option value="koltaka">kolkata</option>-->
  <!--                  <option value="rome">rome</option>-->
  <!--                  <option value="khoksa">khoksa</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/date.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">date</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="26-12-19">23/10/2019</option>-->
  <!--                  <option value="26-12-19">24/10/2019</option>-->
  <!--                  <option value="26-12-19">25/10/2019</option>-->
  <!--                  <option value="26-12-19">26/10/2019</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/cinema.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">cinema</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="Awaken">Awaken</option>-->
  <!--                  <option value="dhaka">dhaka</option>-->
  <!--                  <option value="rosario">rosario</option>-->
  <!--                  <option value="madrid">madrid</option>-->
  <!--                  <option value="koltaka">kolkata</option>-->
  <!--                  <option value="rome">rome</option>-->
  <!--                  <option value="khoksa">khoksa</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--            </form>-->
  <!--          </div>-->
  <!--          <div class="tab-item">-->
  <!--            <form class="ticket-search-form">-->
  <!--              <div class="form-group large">-->
  <!--                <input type="text" placeholder="Search fo Events">-->
  <!--                <button type="submit"><i class="fas fa-search"></i></button>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/city.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">city</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="london">London</option>-->
  <!--                  <option value="dhaka">dhaka</option>-->
  <!--                  <option value="rosario">rosario</option>-->
  <!--                  <option value="madrid">madrid</option>-->
  <!--                  <option value="koltaka">kolkata</option>-->
  <!--                  <option value="rome">rome</option>-->
  <!--                  <option value="khoksa">khoksa</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/date.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">date</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="26-12-19">23/10/2019</option>-->
  <!--                  <option value="26-12-19">24/10/2019</option>-->
  <!--                  <option value="26-12-19">25/10/2019</option>-->
  <!--                  <option value="26-12-19">26/10/2019</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/cinema.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">event</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="angular">angular</option>-->
  <!--                  <option value="startup">startup</option>-->
  <!--                  <option value="rosario">rosario</option>-->
  <!--                  <option value="madrid">madrid</option>-->
  <!--                  <option value="koltaka">kolkata</option>-->
  <!--                  <option value="Last-First">Last-First</option>-->
  <!--                  <option value="wish">wish</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--            </form>-->
  <!--          </div>-->
  <!--          <div class="tab-item">-->
  <!--            <form class="ticket-search-form">-->
  <!--              <div class="form-group large">-->
  <!--                <input type="text" placeholder="Search fo Sports">-->
  <!--                <button type="submit"><i class="fas fa-search"></i></button>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/city.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">city</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="london">London</option>-->
  <!--                  <option value="dhaka">dhaka</option>-->
  <!--                  <option value="rosario">rosario</option>-->
  <!--                  <option value="madrid">madrid</option>-->
  <!--                  <option value="koltaka">kolkata</option>-->
  <!--                  <option value="rome">rome</option>-->
  <!--                  <option value="khoksa">khoksa</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/date.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">date</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="26-12-19">23/10/2019</option>-->
  <!--                  <option value="26-12-19">24/10/2019</option>-->
  <!--                  <option value="26-12-19">25/10/2019</option>-->
  <!--                  <option value="26-12-19">26/10/2019</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--              <div class="form-group">-->
  <!--                <div class="thumb">-->
  <!--                  <img src="/assets/images/ticket/cinema.png" alt="ticket">-->
  <!--                </div>-->
  <!--                <span class="type">sports</span>-->
  <!--                <select class="select-bar">-->
  <!--                  <option value="football">football</option>-->
  <!--                  <option value="cricket">cricket</option>-->
  <!--                  <option value="cabadi">cabadi</option>-->
  <!--                  <option value="madrid">madrid</option>-->
  <!--                  <option value="gadon">gadon</option>-->
  <!--                  <option value="rome">rome</option>-->
  <!--                  <option value="khoksa">khoksa</option>-->
  <!--                </select>-->
  <!--              </div>-->
  <!--            </form>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--  </section>-->
  <!-- ==========Ticket-Search========== -->
</template>
<script>
export default {
  name: 'Header',
};
</script>