<?php
function App_Views__Func(Dja_Request $request)
{
    //$c = Dja_Db_Creation::factory('mysql');
    //$c->createSqlForModel('App_Models__User');
    //$c->createSqlForModel('App_Models__Group');
    //$c->createSqlForModel('App_Models__Message');
    //$c->createSqlForModel('Dja_Contrib_Session_Models__Session');
    
    /*
    $u = Dja_Contrib_Auth_Models__User::objects()->get(1);
    $ugs = $u->groups;
    foreach ($ugs as $ug) {
        echo $ug->name . '<br/>';
    }
    //throw new Dja_Exception('aaaaaaaaa');
    $msgs = $u->msg_in;
    foreach ($msgs as $msg) {
        echo $msg->title . '<br/>';
    }
    */
    //echo dump(CONFIG()->MIDDLEWARE->toArray());
    
    //$query = Dja_Contrib_Auth_Models__User::objects()->get(1);
    
    
    return renderToResponse('test.html');
}

class App_Views__Index extends Dja_Controller
{
    public function index(Dja_Request $request)
    {
        //echo strpos('App_Views_Index', '_');
        /*$u = App_Models_User::objects()->get(1);
        $ugs = $u->groups;
        foreach ($ugs as $u) {
            echo $u->name . '<br/>';
        }*/
        /*try {
            App_Models_Message::metadata();
            $u = App_Models_User::objects()->get(1);
            $msgs = $u->msg_in;
            foreach ($msgs as $msg) {
                echo $msg->title . '<br/>';
            }
            var_dump(App_Models_Message::metadata()->getDbColNames());
            //echo count($msgs);
        } catch (Exception $e) {
            echo $e;
        }
        $users = array();
        for ($i=1;$i<=50;$i++) {
            //$users[] = App_Models_User::objects()->get(1);
        }*/
        
        $c = Dja_Db_Creation::factory('mysql');
        //$c->createSqlForModel('App_Models__User');
        //$c->createSqlForModel('App_Models__Group');
        $c->createSqlForModel('App_Models__Message');
        
        return renderToResponse('test.html');
    }
}
