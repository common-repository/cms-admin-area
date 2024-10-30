<?php
class Admin_Area_Helper{


    public static function currentTabClass($active_url, $active_string){
					echo ($active_url ==$active_string)? 'current-tab':'';
    }

	  public static function getActionLink($tab=''){
				$action = 'options-general.php';

        if(strlen($action)>0){
							$action .= $tab;
				}


		}

	public static function getOptionValue($array, $key){
				if(isset($array[$key])){
					return $array[$key];
				}


			}
  /*get  logo form options array or default logo*/

	public static function getLogoImage($array, $key){
				if(isset($array[$key])){
						return $array[$key];
				}else{
					 return admin_url().'images/wordpress-logo.png';
				}

				}

	public static function getLogoContainerClass($array, $key){
				if(isset($array[$key])){
						return 'custom-image-outer';
				}else{
					 return '';
				}

				}
}
?>