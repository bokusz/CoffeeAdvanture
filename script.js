// ==========================
// KÁVÉKALAND - SCRIPT
// ==========================



// TERMÉK ADATOK

const products = {

    lemonade: {

        title: "Limonádé",

        image: "images/lemonade.jpg",

        description:
        "Frissítő házi limonádé természetes alapanyagokból. Többféle ízben kérhető, tökéletes választás egy meleg napra."

    },


    brownie: {

        title: " Brownie",

        image: "images/brownie.jpg",

        description:
        "Puha, gazdag csokoládés brownie, amely kívül enyhén ropogós, belül pedig kellemesen krémes."

    },


    cookie: {

        title: "🍪 Chocolate Chip Cookie",

        image: "images/cookie.jpg",

        description:
        "Amerikai stílusú vastag cookie rengeteg csokoládédarabkával. Frissen sütve az igazi."

    },


    poffeteg: {

        title: "🍪 Csokis pöffeteg",

        image: "images/poffeteg.jpg",

        description:
        "Omlós csokoládés sütemény porcukros bevonattal, amely szinte elolvad a szádban."

    },


    coffee: {

        title: "☕ Kávé",

        image: "images/coffee.jpg",

        description:
        "Frissen készített kávékülönlegességek, amelyek tökéletesen illenek a süteményeinkhez."

    }

};





// ELEMEK

const cards = document.querySelectorAll(".product-card");

const modal = document.getElementById("productModal");

const modalImage = document.getElementById("modalImage");

const modalTitle = document.getElementById("modalTitle");

const modalDescription = document.getElementById("modalDescription");

const closeButton = document.querySelector(".close");





// TERMÉKRE KATTINTÁS


cards.forEach(card => {


    card.addEventListener("click", () => {


        const productName = card.dataset.product;


        const product = products[productName];


        if(product){


            modalImage.src = product.image;

            modalTitle.textContent = product.title;

            modalDescription.textContent = product.description;


            modal.style.display = "flex";


        }


    });


});






// BEZÁRÁS X-SZEL


closeButton.addEventListener("click", () => {


    modal.style.display = "none";


});






// BEZÁRÁS HÁTTÉRRE KATTINTÁSSAL


modal.addEventListener("click", (event)=>{


    if(event.target === modal){


        modal.style.display = "none";


    }


});






// OLDAL BETÖLTÉSI ANIMÁCIÓ


window.addEventListener("load", ()=>{


    document.body.classList.add("loaded");


});