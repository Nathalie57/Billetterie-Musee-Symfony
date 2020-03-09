const target = document.getElementById("formToComplete");
for(let i=target.dataset.qty; i>=1; i--){
    new TicketForm(i, target);
}