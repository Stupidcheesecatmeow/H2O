const modal = document.getElementById("termsMod");
const openBtn = document.getElementById("openTerms");
const closeBtn = document.querySelector(".close");
const acceptBtn = document.getElementById("aksipT");
const checkbox = document.getElementById("tnc");
const loginBtn = document.getElementById("lgnBtn");
const termsText = document.getElementById("termT");
const scrollMsg = document.getElementById("scrllMsg");


openBtn.onclick = function(e){
  e.preventDefault();
  modal.style.display = "block";
}

closeBtn.onclick = function(){
  modal.style.display = "none";
}

window.onclick = function(e){
  if(e.target == modal){
    modal.style.display = "none";
  }
}

termsText.addEventListener("scroll", function(){
  const atBottom = termsText.scrollTop + termsText.clientHeight >= termsText.scrollHeight - 5;

  if(atBottom){
    acceptBtn.disabled = false;
    scrollMsg.style.display = "none";
  }
});

acceptBtn.onclick = function(){
  checkbox.disabled = false;
  checkbox.checked = true;
  modal.style.display = "none";
  loginBtn.disabled = false;
}

checkbox.addEventListener("change", function(){
  loginBtn.disabled = !this.checked;
});

document.addEventListener("DOMContentLoaded", function(){

// ===== SIDEBAR TOGGLE =====
const toggleBtn = document.getElementById("toggleBtn");
const sidebar = document.getElementById("sidebar");

if(toggleBtn && sidebar){
    toggleBtn.addEventListener("click", function(){

        // desktop toggle
        sidebar.classList.toggle("collapsed");

        // mobile toggle
        sidebar.classList.toggle("active");

    });
}

});