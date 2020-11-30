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
        if(isset($_GET['id_venta'])){
            $ventas = new DataBase('ventas');
            $where = array('id_venta'=>$_GET['id_venta']);
            $res = $ventas->Read($where);
        }else{
            $ventas = new DataBase('ventas');
            $res = $ventas->ReadAll();
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "POST":
        if(isset($_POST['id']) && isset($_POST['nombre'])){
            
            $temas = new DataBase('temas');
            $datos = array(
                'idtema'=>$_POST['id'],
                'nombre'=>$_POST['nombre']
            );
            try{
                $reg = $temas->create($datos);
                $res = array("result"=>"ok","msg"=>"Se guardo el tema", "id"=>$reg);
            }catch(PDOException $e){
                $res = array("result"=>"no","msg"=>$e->getMessage());
            }
            /********************** */
            /*$con=null;
            try{
                $con = new PDO("mysql:host=LOCALHOST;dbname=blogresp", "root", "");
                $con->setAttribute(PDO::ATTR_ERRMODE, 
                    PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $e){
                exit($e->getMessage());
            }
            $stm = $con->preppare("INSERT INTO temas VALUES(:id, :nombre)");
            $stm->bindValue(":id",$_POST['id']);
            $stm->bindValue(":nombre",$_POST['nombre']);
            $stm->execute();
            $res = array("result"=>"ok","msg"=>"Se guardo el tema", "id"=>$reg);*/
            /************************** */
        
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "PUT":
        
    break;
    case "DELETE":
        $data = JWT::get_data($jwt, CONFIG::SECRET_JWT);
        $ventas = new DataBase('ventas');
            if($data['level']=='A'){
                $where = array('id_venta'=>$_GET['id_venta']);
            }else{
                $where = array('id_venta'=>$_GET['id_venta'], 'id_venta'=>$data['id_venta']);
            }
            $where = array('id_venta'=>$_GET['id_venta']);
            $reg = $ventas->delete($where);  
            $res = array("result"=>"ok","msg"=>"Se elimino la venta", "num"=>$reg);
        echo json_encode($res);
    break;
    default:
        header("HTTP/1.1 401 Bad Request");
}