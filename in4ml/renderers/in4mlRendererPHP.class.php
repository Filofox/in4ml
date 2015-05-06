<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlRenderer.class.php' );

/**
 * Simple renderer using PHP
 */
class in4mlRendererPHP extends in4mlRenderer{

	/**
	 * Get form as HTML
	 */
	public function RenderForm( in4mlForm $form, $template_path ){

		// Load templates
		if( !file_exists( $template_path ) ){
			throw new Exception( "Form template '$template_path' not found" );
		}
		include( $template_path );

		if( $override_template_path = in4ml::Config( 'override_renderer_template' ) ){
			$override_template_path .= '.template.php';
			if( !file_exists( $override_template_path ) ){
				throw new Exception( "Form template override '$override_template_path' not found" );
			}
			include( $override_template_path );
			foreach( $templates as $element_type => $element_types ){
				foreach( $element_types as $element => $template ){
					$this->templates[ $element_type ][ $element ] = $template;
				}
			}
		}

		$html = $this->RenderElement( $form->form_element, $form->is_populated );

		return $html;
	}

	/**
	 * Render a form element
	 *
	 * @param		in4mlElement	$element
	 *
	 * @return		string
	 */
	public function RenderElement( in4mlElement $element, $render_values ){

		// These are the values that will be parsed into the template
		$keys = array();
		$values = array();

		// Elements
		$elements = array();
		if( isset( $element->elements ) ){
			$error_html = '';
			foreach( $element->elements as $child_element ){
				$elements[] = $this->RenderElement( $child_element, $render_values );

				if( $child_element->category == 'Field' && $child_element->disabled ){
					$element->AddClass( 'disabled' );
				}

				// Errors
				if( $child_element->category == 'Field' && $errors = $child_element->GetErrors() ){
					$error_container = in4ml::CreateElement( 'Block:Error' );
					$element->AddClass( 'invalid' );
					foreach( $errors as $error ){
						$error_element = in4ml::CreateElement( 'General:Error' );
						$error_element->SetErrorType( $error );
						$error_container->AddElement( $error_element );
					}
					$error_html = $this->RenderElement( $error_container, $render_values );
				}

			}

			if( $element->category == 'Block' && $element->type == 'Form' ){
				$errors = $element->GetErrors();
				if( count( $errors ) ){
					$error_container = in4ml::CreateElement( 'Block:Error' );
					$element->AddClass( 'invalid' );
					foreach( $errors as $error ){
						$error_element = in4ml::CreateElement( 'General:Error' );
						$error_element->SetErrorType( $error );
						$error_container->AddElement( $error_element );
					}
					$error_html = $this->RenderElement( $error_container, $render_values );
				}
			}

			$keys[] = '[[elements]]';
			$values[] = implode( '', $elements );
			$keys[] = '[[error]]';
			$values[] = $error_html;
		}

		if( isset( $element->placeholder ) ){
			$element->AddClass( 'has-label' );
		}
		if( isset( $element->label ) ){
			$element->AddClass( 'has-label' );
		}
		if( isset( $element->notes ) && $element->notes ){
			$element->AddClass( 'has-notes' );
		}
		if( isset( $element->prefix ) ){
			$element->AddClass( 'has-prefix' );
		}
		if( isset( $element->suffix ) ){
			$element->AddClass( 'has-suffix' );
		}
		$element_render_values = $element->GetRenderValues( $render_values );

		// Attributes
		$attributes = array();
		foreach( $element_render_values->GetAttributes() as $key => $value ){
			$attributes[] = $key . '="' . $value . '"';
		}
		$keys[] = '[[attributes]]';
		$values[] = implode( ' ', $attributes );

		// Everything else
		foreach( $element_render_values as $key => $value ){

			// To preserve label width
			if( $key == 'label' && ( $value === '' || $value === null )){
				$value = '&nbsp;';
			}

			$keys[] = '[[' . $key . ']]';

			switch( $key ){
				default:{
					$values[] = $value;
					break;
				}
			}
		}

		// Load the template
		if( $element->template ){
			$template = $element->template;
		} else {
			if( isset( $this->templates[ strtolower( $element_render_values->GetElementCategory() ) ][ strtolower( $element_render_values->GetElementType() ) ] ) ){
				$template = $this->templates[ strtolower( $element_render_values->GetElementCategory() ) ][ strtolower( $element_render_values->GetElementType() ) ];
			} else {
				throw new Exception( 'Template type ' . $element_render_values->GetElementCategory() . ':' . $element_render_values->GetElementType() . ' not found.' );
			}
		}

		return str_replace
		(
			$keys,
			$values,
			$template
		);
	}
}
?>
