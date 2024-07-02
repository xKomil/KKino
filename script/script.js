"use strict";

/**
 * navbar variables
 */

const navOpenBtn = document.querySelector("[data-menu-open-btn]");
const navCloseBtn = document.querySelector("[data-menu-close-btn]");
const navbar = document.querySelector("[data-navbar]");
const overlay = document.querySelector("[data-overlay]");

const navElemArr = [navOpenBtn, navCloseBtn, overlay];

for (let i = 0; i < navElemArr.length; i++) {
  navElemArr[i].addEventListener("click", function () {
    navbar.classList.toggle("active");
    overlay.classList.toggle("active");
    document.body.classList.toggle("active");
  });
}

/**
 * header sticky
 */

const header = document.querySelector("[data-header]");

window.addEventListener("scroll", function () {
  window.scrollY >= 10
    ? header.classList.add("active")
    : header.classList.remove("active");
});

/**
 * go top
 */

const goTopBtn = document.querySelector("[data-go-top]");

window.addEventListener("scroll", function () {
  window.scrollY >= 500
    ? goTopBtn.classList.add("active")
    : goTopBtn.classList.remove("active");
});

function filterMovies(genre) {
  const movies = document.querySelectorAll('.movie-item');
  movies.forEach(movie => {
      if (genre === 'Wszystko' || movie.getAttribute('data-genre') === genre) {
          movie.style.display = '';
      } else {
          movie.style.display = 'none';
      }
  });
}

document.getElementById('trailer-button').addEventListener('click', function() {
  // Pobierz pierwszy film z listy
  const firstMovie = document.querySelector('.movie-item a');
  if (firstMovie) {
      const trailerUrl = firstMovie.getAttribute('href');
      window.open(trailerUrl, '_blank');
  }
});
