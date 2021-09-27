<?php
require 'include/headers_api_php.php';
require 'include/std_messages_api_php.php';


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
                //n = numero de periodos
                //i = taxa de juros
                
                if(count($json_obj["param"]) != 3) {
                    //Bad request
                    $json_output = str_replace('__RESULT__', '"Parametros invalidos. Utilizar: Valor Financiado, Tx. Juros, Periodos"', $JSONStandard[4]);
                } else {
                    
                    $v = $json_obj["param"][0];
                    $i  = $json_obj["param"][1];
                    $n  = $json_obj["param"][2];
                    
                    $strResult = ($v)*((($i/100)*pow((1+($i/100)),$n))/(pow((1+($i/100)),$n) -1));
                    
                    $json_output = str_replace('__RESULT__', $strResult, $JSONStandard[0]);
                    unset($strResult, $v, $i, $n);
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
