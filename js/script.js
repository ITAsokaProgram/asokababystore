<<<<<<< HEAD
const userBox = document.querySelector('.header .flex .account-box');
let navbar = document.querySelector('.header .flex .navbar');

//document.querySelector('#user-btn').onclick = () => {
 //   userBox.classList.toggle('active');
  //  navbar.classList.remove('active');
//};

document.querySelector('#menu-btn').onclick = () => {
    navbar.classList.toggle('active');
    userBox.classList.remove('active');
};

window.onscroll = () => {
    userBox.classList.remove('active');
    navbar.classList.remove('active');
};



document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
=======
const userBox = document.querySelector('.header .flex .account-box');
let navbar = document.querySelector('.header .flex .navbar');

//document.querySelector('#user-btn').onclick = () => {
 //   userBox.classList.toggle('active');
  //  navbar.classList.remove('active');
//};

document.querySelector('#menu-btn').onclick = () => {
    navbar.classList.toggle('active');
    userBox.classList.remove('active');
};

window.onscroll = () => {
    userBox.classList.remove('active');
    navbar.classList.remove('active');
};



document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
>>>>>>> master
