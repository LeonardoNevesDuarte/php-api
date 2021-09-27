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
                        if(validaCNPJ($json_obj["param"][$i])) {
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


function validaCNPJ($cnpj = null) {
    
    // Verifica se um número foi informado
    if(empty($cnpj)) {
        return false;
    }
    
    // Elimina possivel mascara
    $cnpj = preg_replace("/[^0-9]/", "", $cnpj);
    $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);
    
    // Verifica se o numero de digitos informados é igual a 11
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se nenhuma das sequências invalidas abaixo
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cnpj == '00000000000000' ||
        $cnpj == '11111111111111' ||
        $cnpj == '22222222222222' ||
        $cnpj == '33333333333333' ||
        $cnpj == '44444444444444' ||
        $cnpj == '55555555555555' ||
        $cnpj == '66666666666666' ||
        $cnpj == '77777777777777' ||
        $cnpj == '88888888888888' ||
        $cnpj == '99999999999999') {
            return false;
            
            // Calcula os digitos verificadores para verificar se o
            // CNPJ é válido
        } else {
            
            $j = 5;
            $k = 6;
            $soma1 = 0;
            $soma2 = 0;
            
            for ($i = 0; $i < 13; $i++) {
                
                $j = $j == 1 ? 9 : $j;
                $k = $k == 1 ? 9 : $k;
                
                $soma2 += ($cnpj{$i} * $k);
                
                if ($i < 12) {
                    $soma1 += ($cnpj{$i} * $j);
                }
                
                $k--;
                $j--;
                
            }
            
            $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
            $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
            
            return (($cnpj{12} == $digito1) and ($cnpj{13} == $digito2));
            
        }
}


if(isset($JSONStandard)){ unset($JSONStandard);}

if(isset($json_str)){ unset($json_str);}
if(isset($json_obj)){ unset($json_obj);}
if(isset($result)){ unset($result);}

echo $json_output;
?>
