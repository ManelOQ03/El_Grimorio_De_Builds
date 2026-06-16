document.addEventListener("DOMContentLoaded", () => {

    const box = document.getElementById("quote-box");

    if (!box) return;

    async function loadQuote() {
        try {
            box.innerHTML = "Cargando frase épica...";

            const response = await fetch("https://zenquotes.io/api/random");

            if (!response.ok) {
                throw new Error("Error en la respuesta de la API"); 
            }

            const data = await response.json();
            const quote = data[0];

            box.innerHTML = `
                "${quote.q}"
                <br><br>
                <small>- ${quote.a}</small>
            `;

        } catch (error) {
            console.error("ERROR FETCH:", error);
            box.innerHTML = "No se pudo cargar la frase 😢";
        }
    }

    loadQuote();

});