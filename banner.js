const images = document.querySelectorAll('.banner img');
let currentIndex = 0;

function changeImage() {
    images[currentIndex].classList.remove('active'); // Ukryj bieżący obraz
    currentIndex = (currentIndex + 1) % images.length; // Następny indeks (cyklicznie)
    images[currentIndex].classList.add('active'); // Pokaż następny obraz
}

setInterval(changeImage, 3000); // Zmiana obrazu co 3 sekundy
