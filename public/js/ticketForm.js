class TicketForm{
    constructor(id, target){
        let dom = document.createElement("section");
        dom.innerHTML = `
            <h2> élément du ticket ${id}</h2>
            <input name="visitorName_${id}" placeholder="nom du visiteur ${id}">
            <input type="date" name="visitorBirthdayN_${id}" placeholder="date de naissance du visiteur ${id}">
          
            <input type="checkbox" id="reduction${id}" name="reduction${id} 
            <label for="reduction">Bénéficie d'une réduction</label>
        `;
        target.insertBefore(dom, target.firstChild);
    }
}