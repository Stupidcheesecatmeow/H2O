const modal = document.getElementById("termsMod");
const openTerms = document.getElementById("openTerms");
const closeBtn = document.querySelector(".close");

const nextPageBtn = document.getElementById("nextPageBtn");
const acceptBtn = document.getElementById("aksipT");

const loginBtn = document.getElementById("lgnBtn");
const checkbox = document.getElementById("tnc");

const page1 = document.getElementById("page1");
const page2 = document.getElementById("page2");

/* open modal */
openTerms.addEventListener("click", (e) => {
    e.preventDefault();
    modal.style.display = "block";

    page1.style.display = "block";
    page2.style.display = "none";

    acceptBtn.disabled = true;
});

/* close modal */
closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

/* next page */
nextPageBtn.addEventListener("click", () => {
    page1.style.display = "none";
    page2.style.display = "block";

    acceptBtn.disabled = false; 
});

/* aksip */
acceptBtn.addEventListener("click", () => {

    checkbox.disabled = false;
    checkbox.checked = true;

    loginBtn.disabled = false;

    modal.style.display = "none";
});

/* close */
window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

/* forgot pass */
document.addEventListener("DOMContentLoaded", () => {
    const forgotLink = document.querySelector(".login-links a"); 
    
    if (forgotLink) {
        forgotLink.addEventListener("click", function(e) {
            e.preventDefault();
            const target = this.href;
            document.body.style.transition = "opacity 0.25s ease";
            document.body.style.opacity = "0";
            setTimeout(() => { window.location.href = target; }, 250);
        });
    }
});