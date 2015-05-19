<?php

return array(
    // global config params
    'isCacheEnabled' =>true,
    'cachePrefix' => 'nfsscreenwords_',
    // detail method cache time table
    'cacheTime' => array(
        'TrieDao_getTrieInfo' => array(
            'expire' => 600,
            'isUpdate' => false,
            'TruncateCacheFunction' => ''
        ),
        'TrieDao_addWord' => array(
            'expire' => 0,
            'isUpdate' => true,
            'TruncateCacheFunction' => 'getTrieInfo'
        ),
    )
);


?>
