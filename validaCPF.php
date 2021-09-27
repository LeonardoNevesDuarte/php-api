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
        $json_output = $JSONStandard[3];
    } else {
    
        if(isset($json_obj["authKey"])) {
            //Verifica a permissao de uso
            //Como eh publica, todos podem usar
            if(true) {
                
                if($json_obj["param"][0] != "") {
                    //Executa o codigo da API
                    
                    $ary = [];
                    
                    for($i = 0; $i < count($json_obj["param"]); $i++) {
                        if(validaCPF($json_obj["param"][$i])) {
                            array_push($ary,1);
                        } else {
                            array_push($ary,0);
                        }
                    }
                    unset($i);
                    
                    $strResult = "[";
                    $strSeparador = "";
                    for($i = 0; $i < count($ary); $i++) {
                        $strResult = $strResult.$strSeparador.$ary[$i];
                        ($i == 0 ? $strSeparador = "," : true);
                    }
                    unset($i,$strSeparador,$ary);
                    $strResult = $strResult."]";

                    $json_output = str_replace('__RESULT__', $strResult, $JSONStandard[0]);
                    unset($strResult);
                    
                } else {
                    //Server error
                    $json_output = str_replace('__RESULT__', '{}', $JSONStandard[1]);
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


function validaCPF($cpf = null) {
    
    // Verifica se um número foi informado
    if(empty($cpf)) {
        return false;
    }
    
    // Elimina possivel mascara
    $cpf = preg_replace("/[^0-9]/", "", $cpf);
    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
    
    // Verifica se o numero de digitos informados é igual a 11
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se nenhuma das sequências invalidas abaixo
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cpf == '00000000000' ||
        $cpf == '11111111111' ||
        $cpf == '22222222222' ||
        $cpf == '33333333333' ||
        $cpf == '44444444444' ||
        $cpf == '55555555555' ||
        $cpf == '66666666666' ||
        $cpf == '77777777777' ||
        $cpf == '88888888888' ||
        $cpf == '99999999999') {
            return false;
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {
            
            for ($t = 9; $t < 11; $t++) {
                
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            
            return true;
        }
}

if(isset($JSONStandard)){ unset($JSONStandard);}

if(isset($json_str)){ unset($json_str);}
if(isset($json_obj)){ unset($json_obj);}
if(isset($result)){ unset($result);}

echo $json_output;
?>
