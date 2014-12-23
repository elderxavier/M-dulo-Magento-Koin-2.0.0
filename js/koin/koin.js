function verificarTipoPessoa(){
	var pf = $('persona_pf');
	var pj = $('persona_pj');
	var formFisica = $('koin_form_fisica');
	var formJuridica = $('koin_form_juridica');
	
	var tipoPessoa = $$('input[name="billing[tipopessoa]"]');
	
	if ( (pj != undefined && pj.checked) || (tipoPessoa[0] != undefined && tipoPessoa[0].value=='Juridica') ){
		if ($('koin_cpf') != undefined){
			Form.Element.disable('koin_cpf');
		}
		if ($('koin_birthday') != undefined){
			Form.Element.disable('koin_birthday');
		}
		if (formFisica != undefined){
			formFisica.setStyle({ display: 'none' });
		}
		
		if ($('koin_cnpj') != undefined){
			Form.Element.enable('koin_cnpj');
		}
		if ($('koin_founding_date') != undefined){
			Form.Element.enable('koin_founding_date');
		}
		if (formJuridica != undefined){
			formJuridica.setStyle({ display: 'block' });
		}
	} else {
		if ($('koin_cnpj') != undefined){
			Form.Element.disable('koin_cnpj');
		}
		if ($('koin_founding_date') != undefined){
			Form.Element.disable('koin_founding_date');
		}
		if (formFisica != undefined){
			formFisica.setStyle({ display: 'block' });
		}
		if ($('koin_cpf') != undefined){
			Form.Element.enable('koin_cpf');
		}
		if ($('koin_birthday') != undefined){
			Form.Element.enable('koin_birthday');
		}
		if (formJuridica != undefined){
			formJuridica.setStyle({ display: 'none' });
		}
	}
};

function autoCompleteCpfCnpj(){
	if (idInputCpf != ''){
		if (document.getElementById(idInputCpf) != undefined && document.getElementById('koin_cpf') != undefined){
			document.getElementById('koin_cpf').value = document.getElementById(idInputCpf).value;
		}
	}
	if (idInputCnpj != ''){
		if (document.getElementById(idInputCnpj) != undefined && document.getElementById('koin_cnpj') != undefined){
			var inputCnpj = document.getElementById(idInputCnpj);

    		//mesmo ID (OSC)
    		if (idInputCpf == idInputCnpj){
        		if ($$('input[id="'+idInputCnpj+'"]')[1] != undefined){
    				inputCnpj = $$('input[id="'+idInputCnpj+'"]')[1];
        		}
        	}
			document.getElementById('koin_cnpj').value = inputCnpj.value;
		}
	}
}

document.observe('dom:loaded', function() {
	var pf = $('persona_pf');
	var pj = $('persona_pj');
	
	if (pf != undefined){
		pf.observe('click',verificarTipoPessoa);
	}
	if (pj != undefined){
		pj.observe('click',verificarTipoPessoa);
	}
	if ($('p_method_V3W_Koin_Standard') != undefined){
		$(document).on('click', '#p_method_V3W_Koin_Standard',verificarTipoPessoa);
	}
});

function checkTermo(){
	if (document.getElementById('koin_termo_aceite').checked){
		document.getElementById('payment[koin_termo_aceite]').value = '1';
	} else {
		document.getElementById('payment[koin_termo_aceite]').value = '0';
	}
}

function showTermo(){
	var url = 'http://koin.com.br/home/termos';
	
	window.open(url,'Termos de Uso Koin','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=500,height=600');
}

function showDescricao(){
	var url = 'http://www.koin.com.br/about.html';
		
	window.open(url,'O que é Koin?','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=500,height=600');
}