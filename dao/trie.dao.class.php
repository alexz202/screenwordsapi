<?php

/**
 * Created by PhpStorm.
 * User: zhualex
 * Date: 15/5/11
 * Time: 下午1:55
 */
class TrieDao extends AbstractDao
{
    private $m_incFile = "dgc/trie.inc.php";
    private $m_tableName = 'nfsscreenwords';

    public function getTrieInfo()
    {
        if (!file_exists($this->m_incFile)) {
            $this->generateIncFile();
        }
        return require($this->m_incFile);
//        return file_get_contents($this->m_incFile);
    }

    private function generateIncFile()
    {
        $wordsinfo = $this->getAllWords();
        $trieTree = new TrieTree();
        foreach ($wordsinfo as $word) {
            $trieTree->insert($word['words']);
        }
        $triestr = $trieTree->export();
        $items = var_export($triestr,true);
        $items = "<?php return ".$items.";";
        file_put_contents($this->m_incFile,$items);
//        file_put_contents($this->m_incFile,$triestr);
        return $triestr;
    }

    private function getAllWords()
    {
        $sql = 'select words from ' . $this->m_tableName;
        $res = $this->dao->db->ExecQuery($sql, $result);
        return $result;
    }

    public function addWord($word)
    {
        $sql = "insert into $this->m_tableName (words) values('" . $word . "') ";
        $res = $this->dao->db->ExecUpdate($sql);
        if ($res) {
           $this->removeMapFile();
            return true;
        } else
            return false;
    }

    private function removeMapFile()
    {
        if (file_exists($this->m_incFile)) {
            unlink($this->m_incFile);
        }
    }
}