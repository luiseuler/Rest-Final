<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
require_once 'database.php';
require_once 'jwt.php';
if($_SERVER['REQUEST_METHOD']=="OPTIONS"){
    exit();
}

$header = apache_request_headers();
$jwt = trim($header['Authorization']);
switch (JWT::verify($jwt, CONFIG::SECRET_JWT)) {
    case 1:
        header("HTTP/1.1 401 Unauthorized");
        echo "El token no es válido";
        exit();
        break;
    case 2:
        header("HTTP/1.1 408 Request Timeout");
        echo "La sesión caduco";
        exit();
        break;
}

switch($_SERVER['REQUEST_METHOD']){
    case "GET":
        if(isset($_GET['usuario'])){
            $usuarios = new DataBase('usuarios');
            $where = array('usuario'=>$_GET['usuario']);
            $res = $usuarios->Read($where);
        }else{
            $usuarios = new DataBase('usuarios');
            $res = $usuarios->ReadAll();
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "POST":
        if(isset($_POST['id']) && isset($_POST['usuario']) && isset($_POST['fecha_registro']) 
        && isset($_POST['tipo_sangre'])  && isset($_POST['estado_civil']) && isset($_POST['telefono']) 
        && isset($_POST['direccion']) && isset($_POST['email']) && isset($_POST['pass']) && isset($_POST['tipo']) ){
            
            $usuarios = new DataBase('usuarios');
            $datos = array(
                'id'=>$_POST['id'],
                'usuario'=>$_POST['usuario'],
                'fecha_registro'=>$_POST['fecha_registro'],
                'tipo_sangre'=>$_POST['tipo_sangre'],
                'estado_civil'=>$_POST['estado_civil'],
                'telefono'=>$_POST['telefono'],
                'direccion'=>$_POST['direccion'],
                'email'=>$_POST['email'],
                'pass'=>$_POST['pass'],
                'tipo'=>$_POST['tipo']
            );
            try{
                $reg = $usuarios->create($datos);
                $res = array("result"=>"ok","msg"=>"Se guardo el tema", "id"=>$reg);
            }catch(PDOException $e){
                $res = array("result"=>"no","msg"=>$e->getMessage());
            }
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "PUT":
        $data = JWT::get_data($jwt, CONFIG::SECRET_JWT);
        $usuarios = new DataBase('usuarios');
        if(isset($_GET['id']) && isset($_GET['usuario']) && isset($_GET['fecha_registro']) && isset($_GET['tipo_sangre'])
        && isset($_GET['estado_civil']) && isset($_GET['telefono']) && isset($_GET['direccion']) && isset($_GET['email']) 
        && isset($_GET['pass']) && isset($_GET['tipo'])){
            if($data['level']=='A'){
                $where = array('id'=>$_GET['id']);
            }else{
                $where = array('id'=>$_GET['id'], 'id'=>$data['id']);
            }
            $datos = array(
                'id'=>$_GET['id'],
                'usuario'=>$_GET['usuario'],
                'fecha_registro'=>$_GET['fecha_registro'],
                'tipo_sangre'=>$_GET['tipo_sangre'],
                'estado_civil'=>$_GET['estado_civil'],
                'telefono'=>$_GET['telefono'],
                'direccion'=>$_GET['direccion'],
                'email'=>$_GET['email'],
                'pass'=>$_GET['pass'],
                'tipo'=>$_GET['tipo']
            );
            $reg = $usuarios->update($datos,$where);
            $res = array("result"=>"ok","msg"=>"Se guardo el mensaje", "num"=>$reg);
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    case "DELETE":
        $data = JWT::get_data($jwt, CONFIG::SECRET_JWT);
        $usuarios = new DataBase('usuarios');
        if(isset($_GET['usuario'])){
            if($data['level']=='A'){
                $where = array('usuario'=>$_GET['usuario']);
            }else{
                $where = array('usuario'=>$_GET['usuario'], 'usuario'=>$data['usuario']);
            }
            $where = array('usuario'=>$_GET['usuario']);
            $reg = $usuarios->delete($where);  
            $res = array("result"=>"ok","msg"=>"Se elimino el usuario", "num"=>$reg);
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    default:
        header("HTTP/1.1 401 Bad Request");
 }