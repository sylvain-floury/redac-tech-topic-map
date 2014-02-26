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
        $this->where[] =  $element;
    }
    
    /**
     * Gère la condition initiale.
     * 
     * @param String $element
     */
    public function where($element) {
        $this->where[] =  $element;
    }


    /**
     * Construit la requete.
     * 
     * @return String
     */
    public function build() {
        $queryString = 'using o for i"http://psi.ontopedia.net/" subject-identifier('.$this->object.', $ID),';
        
        foreach($this->where as $where) {
            $queryString .= $where .',';
        }
        
        return substr($queryString, 0, -1).'?';
    }
}
