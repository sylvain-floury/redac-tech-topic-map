<?php
namespace RedacTech;

/**
 * Description of Query
 *
 * @author sylvain
 */
class QueryBuilder {
    protected $queryString;
    
    protected $where = array();
    
    protected $join = array();
    
    public function __construct($object= '$Entity') {
        $this->queryString = 'using o for i"http://psi.ontopedia.net/"';
        $this->object = $object;
    }
    
    /**
     * Gère une condition ou logique.
     * 
     * @param String $association
     * @param String $rolePrincipal
     * @param String $roleCritere
     * @param String $values
     * @return String
     */
    public function addOr($association, $rolePrincipal, $roleCritere, $values) {
        if(is_array($values) && count($values) > 1) {
            $criteria = '{';

            foreach($values as $value) {
                $criteria .= 'o:'.$association.'('.$this->object.': o:'.$rolePrincipal.', o:'.$value.': o:'.$roleCritere.')|';
            }
            return substr($criteria, 0, -1).'}';
        }
        elseif(is_array($values) && count($values) == 1) {
            return 'o:'.$association.'('.$this->object.': o:'.$rolePrincipal.', o:'.$values[0].': o:'.$roleCritere.')';
        }
    }
    
    /**
     * Gère une condition et logique
     * 
     * @param String $element
     */
    public function addAnd($element) {
        if($element) {
            $this->where[] =  $element;
        }
    }
    
    /**
     * Gère la condition initiale.
     * 
     * @param String $element
     */
    public function where($element) {
        if($element) {
            $this->where[] =  $element;
        }
    }

    /**
     * Gere les liaisons.
     * 
     * @param String $association
     * @param String $joinEntity
     * @param String $fromRelation
     * @param String $toRelation
     */
    public function join($association, $joinedEntity, $fromRelation = NULL, $toRelation = NULL) {
        if(!is_null($fromRelation) && !is_null($toRelation)) {
            $this->join[] = 'o:'.$association.'('.$this->object.': o:'.$fromRelation.', '.$joinedEntity.': o:'.$toRelation.')';
        }
        else {
            $this->join[] = 'o:'.$association.'('.$this->object.', '.$joinedEntity.')';
        }
    }
    
    /**
     * Permet de selectionner un nombre quelconque de colonnes.
     * 
     */
    public function select() {
        $columns = func_get_args();
        
        if(count($columns) > 0 ) {
            $this->select = 'select ';
            foreach ($columns as $column) {
                $this->select .= $column . ', ';
            }
            $this->select = substr($this->select, 0, -2).' from ';
        }
    }

    /**
     * Construit la requete.
     * 
     * @return String
     */
    public function build() {
        $queryString = 'using o for i"http://psi.ontopedia.net/" ';
        
        $queryString .= $this->select;
        
        $queryString .=' subject-identifier('.$this->object.', $ID),';
        
        foreach($this->where as $where) {
            $queryString .= $where .',';
        }
        
        foreach($this->join as $join) {
            $queryString .= $join .',';
        }
        
        return substr($queryString, 0, -1).'?';
    }
}
