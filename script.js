$(document).ready(() => {

	$('#documentacao').click(() => {
        $('#pagina').load('./documentacao.html');
    })
	$('#suporte').click(() => {
        $('#pagina').load('./suporte.html');
    })
    
    
    //Ajax Http request

    $.ajax({
        type: 'GET',
        url: './app.php',
        data: 'competencia=all',
        dataType: 'json',
        success: (data) => {
            $('#numeroVendas').html(data.numero_de_vendas)
            $('#totalVendas').html('R$ ' + data.total_de_vendas)
            $('#reclamacoes').html(data.contato[0])
            $('#elogios').html(data.contato[1])
            $('#sugestoes').html(data.contato[2])
            $('#clientesAtivos').html(data.clientes[0])
            $('#clientesInativos').html(data.clientes[1])
            $('#despesa').html(data.despesa)       
        },
        error: (err) => {
            console.log(err)

        }

    })

    //request on change select
    $('#competencia').change((e) => {
        const dataValue = $(e.target).val();

        $.ajax({
            type: 'GET',
            url: './app.php',
            data: `competencia=${dataValue}`,
            dataType: 'json',
            success: (data) => {
                $('#numeroVendas').html(data.numero_de_vendas)
                $('#totalVendas').html('R$ ' + data.total_de_vendas)
                $('#reclamacoes').html(data.contato[0])
                $('#elogios').html(data.contato[1])
                $('#sugestoes').html(data.contato[2])
                $('#clientesAtivos').html(data.clientes[0])
                $('#clientesInativos').html(data.clientes[1])       
                $('#despesa').html(data.despesa)       
            },
            error: (err) => {
                console.log(err)

            }

        })
    })
})
