<?php 

namespace Hcode\Model;

use \Hcode\Db\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model {

	const SESSION_ERROR = "OrderError";
	const SESSION_SUCCESS = "OrderSuccess";

	public function save() {
		$sql = new Sql();
		$results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
			":idorder"=>$this->getidorder(),
			":idcart"=>$this->getidcart(),
			":iduser"=>$this->getiduser(),
			":idstatus"=>$this->getidstatus(),
			":idaddress"=>$this->getidaddress(),
			":vltotal"=>$this->getvltotal()
		]);

		if (count($results[0])) {
			$this->setData($results[0]);
		}
	}

	public function get($idorder) {
		$sql = new Sql();
		$results = $sql->select("
			SELECT * 
			FROM tb_orders a
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :idorder", [
				":idorder"=>$idorder
			]);

		if (count($results) > 0) {
			$this->setData($results[0]);
		}
	}

	public static function listAll() {
		$sql = new Sql();
		return $sql->select("
			SELECT * 
			FROM tb_orders a
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			ORDER BY a.dtregister DESC
		");
	}

	public function delete() {
		$sql = new Sql();
		$sql->query("DELETE FROM tb_orders WHERE idorder = :idorder", [
			":idorder"=>$this->getidorder()
		]);
	}

	public function getCard():Cart {
		$cart = new Cart();
		$cart->get((int)$this->getidcart());
		return $cart;
	}

	public static function setMsgError($msg) {
		$_SESSION[Order::SESSION_ERROR] = $msg;
	}

	public static function getMsgError() {
		$msg = (isset($_SESSION[Order::SESSION_ERROR]) && $_SESSION[Order::SESSION_ERROR]) ? $_SESSION[Order::SESSION_ERROR]  : "";
		Order::clearMsgError();
		return $msg;
	}

	public static function clearMsgError() {
		$_SESSION[Order::SESSION_ERROR] = NULL;
	}

	public static function setMsgSuccess($msg) {
		$_SESSION[Order::SESSION_SUCCESS] = $msg;
	}

	public static function getMsgSuccess() {
		$msg = (isset($_SESSION[Order::SESSION_SUCCESS]) && $_SESSION[Order::SESSION_SUCCESS]) ? $_SESSION[Order::SESSION_SUCCESS]  : "";
		Order::clearMsgSuccess();
		return $msg;
	}

	public static function clearMsgSuccess() {
		$_SESSION[Order::SESSION_SUCCESS] = NULL;
	}	

	public static function getPage($page = 1, $itemsPerPage = 10) {
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_orders a
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			ORDER BY a.dtregister DESC
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

		return [
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}	

	public static function getPageSearch($search = 1, $page = 1, $itemsPerPage = 10) {
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_orders a
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :id OR f.desperson LIKE :search
			ORDER BY a.dtregister DESC
			LIMIT $start, $itemsPerPage;
		", [
			":search"=>"%" . $search . "%",
			"id"=>$search
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

		return [
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}	
}

 ?>
