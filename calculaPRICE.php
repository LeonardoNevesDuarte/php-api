<?php
require 'include/headers_api_php.php';
require 'include/std_messages_api_php.php';

//Calculo do PRICE - Sistema Frances de Amortizacao
//Baseado em
//Periodo – o mes do pagamento
//Prestacao – para o valor da prestacao
//Amortizacao – para o valor da amortizacao
//Juros – para o valor dos juros
//Saldo devedor – para o valor do saldo devedor

# Get JSON as a string
$json_str = file_get_contents('php://input');

# Get as an object
$json_obj = json_decode($json_str, true);

if(count($json_obj) < 2) {
    $json_output = $JSONStandard[3];
} else {

    if(!isset($json_obj["authKey"]) || !isset($json_obj["param"])) {
        //Bad request
        $json_output = $JSONStandard[3];
    } else {
    
        if(isset($json_obj["authKey"])) {
            //Verifica a permissao de uso
            //Como eh publica, todos podem usar
            if(true) {
                
                //Executa o codigo da API
                //Parametros esperados
                //V = valor financiado
                //i = taxa de juros
                //n = numero de periodos
                
                if(count($json_obj["param"]) != 3) {
                    //Bad request
                    $json_output = str_replace('__RESULT__', '"Parametros invalidos. Utilizar: Valor Financiado, Tx. Juros, Periodos"', $JSONStandard[4]);
                } else {
                    
                    $v  = $json_obj["param"][0];
                    $i  = $json_obj["param"][1];
                    $n  = $json_obj["param"][2];
                    
                    $aryResultado = [];
                    
                    $pmt = $strResult = ($v)*((($i/100)*pow((1+($i/100)),$n))/(pow((1+($i/100)),$n) -1));
                    $juros = $v*($i/100);
                    
                    $amort = $pmt-$juros;
                    $saldo = $v-$amort;
                    
                    
                    for($a = 0; $a<$n; $a++) {
                        //Colunas Mes / Prestacao / Amortizacao / Juros / Saldo Devedor
                        $aux = array(($a+1), round($pmt,2), round($amort,2), round($juros,2), round($saldo,2));
                        array_push($aryResultado, $aux);
                        
                        $juros = $saldo*($i/100);
                        $amort = $pmt-$juros;
                        $saldo = $saldo-$amort;

                    }
                    unset($a);
                    
                    $strSep = "";
                    $strResult = '[';
                    for($a = 0; $a < count($aryResultado); $a++) {
                        $strResult = $strResult.$strSep."[".$aryResultado[$a][0].",".$aryResultado[$a][1].",".$aryResultado[$a][2].",".$aryResultado[$a][3].",".$aryResultado[$a][4]."]";
                        if($a == 0) { $strSep = ","; } 
                    }
                    unset($a, $strSep);
                    $strResult = $strResult.']';

                    $json_output = str_replace('__RESULT__', $strResult, $JSONStandard[0]);
                    unset($strResult, $v, $i, $n);
                    unset($juros, $pmt, $amort,$saldo, $aryResultado);
                }

            } else {
                //Forbidden
                $json_output = $JSONStandard[2];
            }
            
        } else {
            //Forbidden
            $json_output = $JSONStandard[2];
        }
        
    }
}


if(isset($JSONStandard)){ unset($JSONStandard);}

if(isset($json_str)){ unset($json_str);}
if(isset($json_obj)){ unset($json_obj);}
if(isset($result)){ unset($result);}

echo $json_output;
?>
