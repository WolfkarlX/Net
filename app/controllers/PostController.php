<?php 

namespace app\controllers;

use app\models\comments;
use app\models\publication;
use app\models\reaction_type;
use app\models\reactions_publications;
use app\models\topics;
use LDAP\Result;

class PostController{

    public function index(){
        require_view("error404");
    }
    
    //Obtiene para obtener publicaciones
    public function getP(){
        if(!empty($_GET)){
            $pp = in_array('_pp', array_keys(filter_input_array(INPUT_GET)));
            if($pp){
                print_r($this->getPost());
            }else{
                require_view("error404");
                die();
            }
        }else{
            require_view("error404");
            die();
        }
    }
   
    //Obtiene para crear publicacion
    public function get_Publidata(){
        if(!empty($_POST)){
            $cp = in_array('_cp', array_keys(filter_input_array(INPUT_POST)));
            if($cp){
                $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
                print_r($this->createPost($datos));
            }else{
                require_view("error404");
            }
        }else{
            require_view("error404");
        }

    }

    //obtiene para obtener publicaciones de un usuario en especifico
    public function get_user_P(){
        if(!empty($_GET)){
            $up = in_array('uid', array_keys(filter_input_array(INPUT_GET)));
            if($up){
                $key = filter_input_array(INPUT_GET)["uid"];
                print_r($this->getPost("","", $key));
            }else{
                require_view("error404");
                die;
            }
        }else{
            require_view("error404");
            die;
        }
    }

    //Obtiene para obtener temas de todas las publicaciones
    public function getT(){
        if(!empty($_GET)){
            $gt = in_array('_gt', array_keys(filter_input_array(INPUT_GET)));
            if($gt){
                print_r(self::getTopics());
            }else{
                require_view("error404");
                die();
            }
        }else{
            require_view("error404");
            die();
        } 
    }
 //---------------------------- Modelos y Bd----------------------------------------------------------------------------------------


//OBTENER LA CUENTA DE CADA TIPO DE REACCION DE CADA PUBLICACION
    private function getPost($limit="", $pid = "", $uid = ""){
        $posts = new publication();
        $resultP = $posts->select(['a.ID_publication', 'a.Title', 'a.Content', 'b.Username','t.Name as topic', 'a.Date'])
                        ->count([["DISTINCT rp.ID_reaction", "reacciones"], ["DISTINCT c.ID_comment", "comments"]])
                         ->group_concat("DISTINCT rt.ID_type", "reacciones_IDS")
                         ->join([['user b', 'a.ID_user = b.ID_user', " "], 
                         ["reactions_publications rp", "a.ID_publication = rp.ID_publication", "left"],
                         ["reactions_comments rc", "a.ID_publication = rc.ID_comment", "left"],
                         ["reaction_type rt", "rp.ID_type = rt.ID_type OR rc.ID_type = rt.ID_type", "left"],
                         ["comments c", "a.ID_publication = c.ID_publication", "left"], 
                         ["topics t", "a.ID_topic = t.ID_topic", "inner"]
                         ])
                         ->where($pid != "" ? [['a.ID_publication', $pid], ["a.Active", 1]] : [["a.Active", 1]]) //corregir el obtener el id de a publi si no se especifica
                         ->where($uid != "" ? [['a.ID_user', $uid], ["a.Active", 1]] : [["a.Active", 1]])
                         ->groupby("a.ID_publication DESC")
                         ->limit($limit)
                         ->getAll();
                        return json_encode($resultP, JSON_UNESCAPED_UNICODE);
    }
    //LO COMENTE ESPERANDO REUTILIZARLO PARA CUANDO SE OBTENGA UNA SOLA PUBLICACION
        /*if($pid!="" || $limit==1){
            $comments = new comments();
            $resultC = $comments->select(['id'])
                                ->count()
                                ->where([['postId',json_decode($resultP)[0]->id]])
                                ->getAll();
            //$interacts = new ();
            $resultL = $interacts->select(['id'])
                                 ->count()
                                 ->where([['postId',json_decode($resultP)[0]->id],['tipo',1]])
                                 ->get();
            $resultML = $interacts->select(['id'])
                                  ->count()
                                  ->where([['postId',json_decode($resultP)[0]->id],['tipo',1],
                                             ['userId',$this->userId]])
                                  ->get();
            $resultLo = $interacts->select(['id'])
                                  ->count()
                                  ->where([['postId',json_decode($resultP)[0]->id],['tipo',2]])
                                  ->get();
             $resultMLo = $interacts->select(['id'])
                                   ->count()
                                   ->where([['postId',json_decode($resultP)[0]->id],['tipo',2],
                                              ['userId',$this->userId]])
                                   ->get();
            $resultH = $interacts->select(['id'])
                                  ->count()
                                  ->where([['postId',json_decode($resultP)[0]->id],['tipo',3]])
                                  ->get();
             $resultMH = $interacts->select(['id'])
                                   ->count()
                                   ->where([['postId',json_decode($resultP)[0]->id],['tipo',3],
                                              ['userId',$this->userId]])
                                   ->get();
            $result = json_encode(array_merge(
                        json_decode($resultP),
                        json_decode($resultC),
                        json_decode($resultL),
                        json_decode($resultML),
                        json_decode($resultLo),
                        json_decode($resultMLo),
                        json_decode($resultH),
                        json_decode($resultMH)));
        }else{
            $result = $resultP;
        }
        return $result;
        
    }*/
    //Crear Publoicacion
    
    private function createPost($datos){
        $post = new publication();
        $post->setValores([$datos["titulo"], $datos["contenido"], $datos["date"], $datos["key"], $datos["tid"]]);
        $result = $post->insert();
        return $result;
    }

    private function getTopics(){
        $topic = new topics();
        $result = $topic->select(["ID_topic, Name"])->getAll();
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }





}


?>