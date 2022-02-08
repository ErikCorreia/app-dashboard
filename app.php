<?php

    class Dashboard {
        public $data_inicio;
        public $data_fim;
        public $numero_de_vendas;
        public $total_de_vendas;
        public $despesa;
        public $contato  = [];
        public $clientes  = [];

        //recupera o valor de uma das variaveis dinamicamente
        public function __get($attr) {
            return $this->$attr;
        }

        //seta valores para as variaveis da classe
        public function __set($attr, $value) {
            $this->$attr = $value;
            return $this;
        }
    }

    class Connection {
        private $host = "localhost";
        private $dbname = "dashboard";
        private $user = 'root';
        private $password = '';

        public function connect(){
            try{

                $connect = new PDO(
                    "mysql: host=$this->host; dbname=$this->dbname", 
                    "$this->user", 
                    "$this->password",
                );
                
                $connect->exec('set charset utf8');
                return $connect;

            }catch(PDOException $e){
                echo '</pre>' .$e->getMessage(). '</pre>';
            }
        }
    }

    class ServiceDB {
        private $connection;
        private $dashboard;

        public function __construct(Connection $connection, Dashboard $dashboard){
            $this->connection = $connection->connect();
            $this->dashboard = $dashboard;
        }

        public function getTotalDespesas(){
            $query = ("SELECT sum(total) as despesa FROM tb_despesas WHERE data_despesa BETWEEN :data_inicio and :data_fim");
                    
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
            $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ)->despesa;
        }

        //recuperar a quantidade total de clientes ativos e inativos
        public function getClientesAtivosInativos(){
            $queryAtivos = (" SELECT count(*) as ativos FROM `tb_clientes` WHERE cliente_ativo = 1;");
            $queryInativos = (" SELECT count(*) as inativos FROM `tb_clientes` WHERE cliente_ativo = 0;");
            $stmtAtivos = $this->connection->prepare($queryAtivos);
            $stmtInativos = $this->connection->prepare($queryInativos);
            $stmtAtivos->execute();
            $stmtInativos->execute();
            return [
                0 => $stmtAtivos->fetch(PDO::FETCH_OBJ)->ativos,
                1 => $stmtInativos->fetch(PDO::FETCH_OBJ)->inativos
            ];
        }

        //recupera a quantidade de fidback por tipo
        public function getContactData(){

            $queryReclamacoes = (" SELECT COUNT(*) as reclamacoes FROM tb_contatos WHERE tipo_contato = 1");
            $queryElogios = (" SELECT COUNT(*) as elogios FROM tb_contatos WHERE tipo_contato = 2");
            $querySugestoes = (" SELECT COUNT(*) as sugestoes FROM tb_contatos WHERE tipo_contato = 3");
            
            $stmtReclamacoes = $this->connection->prepare($queryReclamacoes);
            $stmtElogios = $this->connection->prepare($queryElogios);
            $stmtSugestoes = $this->connection->prepare($querySugestoes);

            $stmtReclamacoes->execute();
            $stmtElogios->execute();
            $stmtSugestoes->execute();
            
            return array(
                0 => $stmtReclamacoes->fetch(PDO::FETCH_OBJ)->reclamacoes,
                1 => $stmtElogios->fetch(PDO::FETCH_OBJ)->elogios,
                2 => $stmtSugestoes->fetch(PDO::FETCH_OBJ)->sugestoes
            );

        }
        
        //recupera a quantidade de vendas entre determinado periodo expecificado
        public function getNumeroVendas(){
            $query = "
                SELECT 
                count(*) as numero_de_vendas 
                FROM 
                    tb_vendas 
                    WHERE data_venda BETWEEN :data_inicio and :data_fim";
                    
                    $stmt = $this->connection->prepare($query);
                    $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
            $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ)->numero_de_vendas;
        }
        
        //recupera o valor total de vendas em determinado periodo expecificado
        public function getTotalVendas(){
            $query = "
                SELECT 
                    sum(total) as total_de_vendas 
                FROM 
                    tb_vendas 
                WHERE data_venda BETWEEN :data_inicio and :data_fim";

            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
            $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ)->total_de_vendas;
        }
    }

    $dashboard = new Dashboard();
    $connection = new Connection();
    
    if(isset($_GET['competencia']) && $_GET['competencia'] !== 'all') {
    
        $limitData = explode('-', $_GET['competencia']);
        $year = $limitData[0];
        $month = $limitData[1];
        $monthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
    
        $dashboard->__set('data_inicio',$year .'-'. $month  .'-01');
        $dashboard->__set('data_fim', $year .'-'. $month .'-'. $monthDays);
    
        
    }else if(!isset($_GET['competencia']) || $_GET['competencia'] == 'all'){
        
        
        $dashboard->__set('data_inicio', '2021-01-01');
        $dashboard->__set('data_fim', '2021-12-31');
        
        
    }
    
    $serviceDB = new ServiceDB($connection, $dashboard);
    
    $dashboard->__set('numero_de_vendas', $serviceDB->getNumeroVendas());
    $dashboard->__set('total_de_vendas', $serviceDB->getTotalVendas());
    
    $dashboard->__set('contato', $serviceDB->getContactData());
    $dashboard->__set('clientes', $serviceDB->getClientesAtivosInativos());
    $dashboard->__set('despesa', $serviceDB->getTotalDespesas());

    echo json_encode($dashboard);
    

?>