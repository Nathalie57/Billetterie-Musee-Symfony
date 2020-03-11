class TicketForm{
    constructor(id, target){
        let dom = document.createElement("section");
        dom.innerHTML = `
            <h2> élément du ticket ${id}</h2>
            <label for="visitorName">Nom et prénom</label>
            <input name="visitorName_${id}" placeholder="nom du visiteur ${id}">
            <label for="visitorBirthday">Date de naissance</label>
            <input type="date" name="visitorBirthdayN_${id}" placeholder="date de naissance du visiteur ${id}">
            <label for="reduction">Bénéficie d'une réduction</label>
            <input type="checkbox" id="reduction${id}" name="reduction${id} 
        `;
        target.insertBefore(dom, target.firstChild);
    }
}