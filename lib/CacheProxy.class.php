<?php
/**
 * Memcache 的代理类
 * 
 * @author Jensenzhang
 */
class CacheProxy {

    private $dao;
    private $cacheConfig;

    public function __construct($dao) {
        $this->cacheConfig = require ALL_CACHE_INFO;
        $this->dao = $dao;
    }

    /**
     * call all method provided by dao
     * @param type $name
     * @param type $arguments
     * @return type 
     */
    public function __call($methodName, $arguments) {
        // check the give method exist
        if (method_exists($this->dao, $methodName)) {
            // check global config if use cache or not
            if ($this->isUseCache()) {
                
                // Get类型， 只是读取，set Memcache
                $classname = get_class($this->dao);
                  $expireKey = $classname . "_" . $methodName;
               $cacheItem = $this->getCacheItem($expireKey);
                // 如果是Update/Add/Delete 操作
                // 删除Memcache
                if( $cacheItem['isUpdate'] ){
                    $updateMethodName = $cacheItem['TruncateCacheFunction'];
                    $updateKey = $this->getKey( $classname, $updateMethodName, $arguments );
                    CommonMemcache::delete($updateKey);
                }
                
                // 缓存配置不存在
                if (!$this->isCacheItemExist($expireKey)||$cacheItem['isUpdate']) {
                    $result = call_user_func_array(array($this->dao, $methodName), $arguments);
                }
                // 使用缓存
                else {
                    $key = $this->getKey( $classname, $methodName, $arguments );
                    // 缓存是否存在
                    if (!( $result = CommonMemcache::get($key) )) {
                        $result = call_user_func_array(array($this->dao, $methodName), $arguments);
                        
                        // 存配置数组获得缓存过期时间
                        $expire = $cacheItem['expire'];

                        // 设置缓存
                        CommonMemcache::set($key, $result, 0, $expire);
                        // just testing here
                        OSS_LOG(__FILE__, __LINE__, LP_DEBUG, "not using cache " . $key . " expire time is :" . $expire . "\n");
                    } else {
                        OSS_LOG(__FILE__, __LINE__, LP_DEBUG, "using cache " . $key . "\n");
                    }
                }

                return $result;
            }
            else{
                // call function directly
                $result = call_user_func_array(array($this->dao, $methodName), $arguments);
                return $result;
            }
        } 
        else {
            OSS_LOG(__FILE__, __LINE__, LP_ERROR, "method: $methodName does not exist\n");
            return false;
        }
    }

    private function getKey( $classname, $methodName, $arguments ){
        return $this->getCachePrefix() . strtolower($classname . $methodName);
    }
    
    private function isUseCache() {
        return $this->cacheConfig['isCacheEnabled'];
    }

    private function getCachePrefix() {
        return $this->cacheConfig['cachePrefix'];
    }

    private function isCacheItemExist($key) {
        return isset($this->cacheConfig['cacheTime'][$key]);
    }

    /**
     * @global type $cacheTable
     * @param type $key
     * @return type
     */
    private function getCacheItem($key) {

        if (isset($this->cacheConfig['cacheTime'][$key])) {
            return $this->cacheConfig['cacheTime'][$key];
        }

        return 0;
    }
}

?>
