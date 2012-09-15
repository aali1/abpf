<?php
	
	class HTML {
		
		/**
		 *	Format an array of options for use in an HTML select box
		 *	@param	array	$options		An associative array of options to display. Can be an array of arrays to use <optgroup> elements
		 *	@param	mixed	$selected		The value of the selected option (key from $options)
		 *	@param	bool	$key_eq_val		Use the array value as the key
		 *	@return	string					The formatted HTML of the options
		 */
		public static function selectOptions($options, $default = null, $key_eq_val = false) {
			if (!is_array($options) || !count($options)) {
				return '';
			}
			$html = '';
			// loop over our options and display them
			foreach ($options as $key => $var) {
				// if its an array we use optgroup elements
				if (is_array($var)) {
					$html .= sprintf('<optgroup label="%s">%s</optgroup>', $key, $this->selectOptions($var, $default, $key_eq_val));
				} else {
					if($key_eq_val) {
						$key = $var;
					}
					$selected = ((is_array($default) && in_array($key, $default)) || $key == $default ? ' selected="selected"' : '');
					$html .= sprintf('<option value="%s"%s>%s</option>', $key, $selected, $var);
				}
			}
			return $html;
		}
		
		/**
		 *	Output <script> tags for including javascript
		 *	@return		string HTML <script> tag
		 */
		public static function js() {
			$s = '';
			foreach(func_get_args() as $js) {
				$file = WEB_ROOT . '/' . $js;
				if(!file_exists($file)) {
					ABPF::logger()->error("JS file $file doesn't exist!");
					continue;
				}
				$v = filemtime($file);
				$s .= sprintf('<script type="text/javascript" src="%s/%s?v=%d"></script>', BASE_URL, $js, $v);
			}
			return $s;
		}
		
		/**
		 *	Output <link> tags for including css files
		 *	@return		string HTML <link> tag
		 */
		public static function css() {
			$s = '';
			foreach(func_get_args() as $css) {
				$file = WEB_ROOT . '/' . $css;
				if(!file_exists($file)) {
					ABPF::logger()->error("CSS file $file doesn't exist!");
					continue;
				}
				$v = filemtime($file);
				$s .= sprintf('<link rel="stylesheet" type="text/css" href="%s/%s?v=%d" />', BASE_URL, $css, $v);
			}
			return $s;
		}
		
		
		/**
		 *	Output <img> tags for including css files
		 *	@param		string $img
		 *	@param		string alt
		 *	@param		string $title
		 *	@return		string HTML <img> tag
		 */
		public static function img($img, $alt, $title = '') {
			$file = WEB_ROOT . '/' . $img;
			if(!file_exists($file)) {
				ABPF::logger()->error("Image file $file doesn't exist!");
				return '';
			}
			$v = filemtime($file);
			return sprintf('<img src="%s/%s?v=%d" alt="%s" title="%s" />', BASE_URL, $img, $v, $alt, $title);
		}
		
		
		/**
		 *	Create a link (<a href=""></a>)
		 *	@param		string $location
		 *	@param		string $text
		 *	@param		string $title
		 *	@return		string HTML <a> tag
		 */
		public function link($href, $text = null, $title = '') {
			$text = $text ? $text : $href;
			return sprintf('<a href="%s" title="%s">%s</a>', $href, $title, $text);
		}
		
		/**
		 *	Encode HTML special chars
		 *	@param		string $html
		 *	@return		string $html with special chars encoded
		 */
		static public function encode($html) {
			return htmlspecialchars($html);
		}
		
		/**
		 *	Decode HTML special chars
		 *	@param		string $html
		 *	@return		string $html with special chars decoded
		 */
		static public function decode($html) {
			return htmlspecialchars_decode($html);
		}
	}
	