<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');

require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session
header('Content-Type: text/html; charset=utf-8');
if(!$conf['remote']) die((new IXR_Error(-32605, "JSON-RPC server not enabled."))->getXml());

require_once(DOKU_INC.'inc/jsonrpc_core.php');
// ... it is a form ...
// a fake jsonrpc
/* {
  "Content-Type":"application/x-www-form-urlencoded",
  "Authorization": "Basic YWRtaW46MTIz"
}

{
  request:{
    "jsonrpc": "2.0", 
    "method": "wiki.getPage", 
    "params": ["start"], 
    "id": 3
    }
}

需要打开 xmlrpc, 并且清空 下面的禁止用户访问列表
*/
class jsonrpc {
    protected $server;
    public function __construct(){
      $this->server=new jsonrpc_server();
    }

    public function res()
    {
        //e.g. access additional request variables
        global $INPUT; //available since release 2012-10-13 "Adora Belle"
        try {
            $rpc_request=$INPUT->str('request');
            
            $rt=$this->server->rpc($rpc_request);

            //json library of DokuWiki
            require_once DOKU_INC . 'inc/JSON.php';
            $json = new JSON();
            //set content type
            if($INPUT->str("callback")){
                echo $INPUT->str("callback")."(".$rt.")";
            }else {
                echo $rt;
            }
        } catch (RemoteAccessDeniedException $e) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                http_status(401);
                echo "server error. not authorized to call method $methodname";
            } else {
                http_status(403);
                echo "server error. forbidden to call the method $methodname";
            }
        } catch (RemoteException $e) {
            http_status(403);
            echo "RemoteException $e";
        }
    }
}

$server = new jsonrpc();

$server->res();

// vim:ts=4:sw=4:et:
