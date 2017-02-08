<?php
/*
 * hash一致性算法
 * */
class HashRedis{
    private static $_vNode=12; //虚拟节点数
    private static $_mapHashServer;
    private static $_nodeList = [];

    //hash加密
    /**
     * @param $string
     * @return int
     */
    protected static function toHash($string){
        return crc32($string);
    }

    //添加节点
    /**
     * @param string|array $node
     * eg: 192.168.1.2 | array('192.168.1.1','192.168.1.2','192.168.1.3','192.168.1.4','192.168.1.5')
     * @throws Exception
     */
    public static function addNode($node){
        if(is_array($node)){
            self::$_nodeList = array_merge(self::$_nodeList,$node);
        }else{
            self::$_nodeList[] = $node;
        }
        //n**2
        foreach (self::$_nodeList as $v){
            for($i=0;$i< self::$_vNode;$i++){
                $tmp = self::toHash($v.$i);
                if(!isset(self::$_mapHashServer[$tmp])){
                    self::$_mapHashServer[$tmp] = $v;
                }else{
                    throw new Exception('repeat:'.$v);
                }
            }
        }
        ksort(self::$_mapHashServer);
    }

    //获取字符的KEY和所在服务器
    /**
     * @param $string
     * @return array
     * Array(
     *  [key] => 531379839
     *  [server] => 192.168.1.2
     * )
     */
    public static function mapServer($string){
        $tmp = [];
        $tmp['key'] = self::toHash($string);
        foreach (self::$_mapHashServer as $k=>$v){
            if($k > $tmp['key']){
                $tmp['server'] = $v;
                break;
            }
        }
        return $tmp;
    }

    //删除节点
    /**
     * @param $server
     */
    public function removeNode($server){
        if(in_array($server,self::$_nodeList)){
            foreach (self::$_mapHashServer as $k=>$v){
                if($v == $server){
                    unset(self::$_mapHashServer[$k]);
                }
            }
        }
    }

    //设置虚拟节点数
    /**
     * @param $num
     */
    public static function setvNote($num){
        self::$_vNode = is_int($num) ? $num : 1;
    }
}
HashRedis::addNode(array('192.168.1.1','192.168.1.2','192.168.1.3','192.168.1.4','192.168.1.5'));
$tmp = HashRedis::mapServer('334q2');
print_r($tmp);
/**
 * result:
 *  Array
 *  (
 *      [key] => 531379839
 *      [server] => 192.168.1.2
 *  )
 * */
