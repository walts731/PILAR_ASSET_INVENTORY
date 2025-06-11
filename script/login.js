document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
            }
        });

        const typingElements = document.querySelectorAll('.typing-text');

        function typeText(element, text, index = 0) {
            if (index < text.length) {
                element.textContent += text.charAt(index);
                setTimeout(() => typeText(element, text, index + 1), 40);
            }
        }

        function resetAndType(activeSlide) {
            typingElements.forEach(el => el.textContent = ''); // Clear all
            const activeTextEl = activeSlide.querySelector('.typing-text');
            const text = activeTextEl.getAttribute('data-text');
            typeText(activeTextEl, text);
        }

        // Initial typing
        const firstSlide = document.querySelector('.carousel-item.active');
        resetAndType(firstSlide);

        // Handle typing on slide change
        const carousel = document.getElementById('welcomeCarousel');
        carousel.addEventListener('slid.bs.carousel', () => {
            const activeSlide = document.querySelector('.carousel-item.active');
            resetAndType(activeSlide);
        });

        document.addEventListener("DOMContentLoaded", function() {
            const loginBtn = document.getElementById("loginBtn");

            loginBtn.addEventListener("click", function() {
                loginBtn.classList.remove("animate-button"); // Reset animation
                void loginBtn.offsetWidth; // Trigger reflow to restart animation
                loginBtn.classList.add("animate-button");
            });
        });