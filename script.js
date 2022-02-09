const apiRequest = (filter) => {
    
    $.ajax({
        type: 'GET',
        url: './app.php',
        data: `competencia=${filter}`,
        dataType: 'json',
        success: (data) => {
            $('#numeroVendas').html(data.numero_de_vendas ? data.numero_de_vendas : '0');
            $('#totalVendas').html(data.total_de_vendas ? `R$ ${data.total_de_vendas}` : '0');
            $('#reclamacoes').html(data.contato[0] ? data.contato[0] : '0');
            $('#elogios').html(data.contato[1] ? data.contato[1] : '0');
            $('#sugestoes').html(data.contato[2] ? data.contato[2] : '0');
            $('#clientesAtivos').html(data.clientes[0] ? data.clientes[0] : '0')
            $('#clientesInativos').html(data.clientes[1] ? data.clientes[1] : '0')
            $('#despesa').html( data.despesa ? `R$ ${data.despesa}` : '0')       
        },

        error: (err) => {
            console.log(err);

        }
    })
}

$(document).ready(() => {

    apiRequest('all');

	$('#documentacao').click(() => {
        $('#pagina').load('./documentacao.html');
    })
	$('#suporte').click(() => {
        $('#pagina').load('./suporte.html');
    })

    //request on change select
    $('#competencia').change((e) => {
        const filterData = $(e.target).val();

        apiRequest(filterData);
    })
})
