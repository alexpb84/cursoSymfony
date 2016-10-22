<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }
    
    public function loginAction(Request $request){
        $helpers = $this->get("app.helpers");
        $jwt_auth = $this->get("app.jwt_auth"); 
        
        // Recibir json por POST
        $json = $request->get("json", null);
        
        if( $json != null ){
            $params = json_decode($json);
            
            $email = (isset($params->email)) ? $params->email : null; 
            $password = (isset($params->password)) ? $params->password : null; 
            $getHash = (isset($params->gethash)) ? $params->gethash : null; 
            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Incorrect  email my brother!!!";
            
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            // Cifrar la password
            $pwd = hash('sha256', $password);
            
            if(count($validate_email) == 0 && $password != null){
                
                if($getHash == null){
                    $signup = $jwt_auth->signup($email, $pwd);
                    
                }else{
                    $signup = $jwt_auth->signup($email, $pwd, true);
                }
                return new JsonResponse($signup);
                
            }else{
                return $helpers->json(
                        array("status"=>"error", 
                                "data"=>"Login not valid")
                        );
            }
        }else{
            return $helpers->json(
                        array("status"=>"error", 
                                "data"=>"Send json with POST !!!")
                        );
        }
    }
    
    /**
     * @Route("/pruebas", name="pruebas")
     */
    public function pruebasAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        
        $hash = $request->get("authoritation", null);
        $check = $helpers->authCheck($hash, true);
        
        var_dump($check);
        die();
        
        /*$em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('BackendBundle:User')->findAll();
        
        return $helpers->json($users);
        
         */
    }
    
    
}
