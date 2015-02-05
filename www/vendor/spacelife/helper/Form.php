<?php

namespace spacelife\helper;

use spacelife\core\Router;
use spacelife\tools\Collection;
use spacelife\exception\SLException;

/**
* Form
*/
class Form
{
    protected $data = false;
    protected $errors = false;

    protected $config = false;
    protected $translate = false;

    private $session_csrf = false;

    public function __construct(array $params)
    {
        $this->data         = $params['data'] === false ? new Collection([]) : new Collection($params['data']);
        $this->errors       = $params['errors'] === false ? new Collection([]) :new Collection($params['errors']);
        $this->translate    = $params['translation'];
        $this->btn          = $params['btn'];
        $this->config       = $params['config'];
        $this->session_csrf = $params['session_csrf'];

    }

    /**
    *   start
    *
    **/
    public function start(array $opt)
    {
        //  setting method to post by default (what else !)
        $method = 'post';
        if (isset($opt['method'])) {
            $method = $opt['method'];
        }

        $url = '';
        $id_field = '';
        $url_id = '';
        if (isset($opt['url'])) {
            $url = $opt['url'];
            //  if data contains an ID
            if ($this->data->id) {
                //  if it's not disabled ... add it to url and create an hidden field
                if (array_search('disable_id', $opt) === false) {
                    $url_id = '/'.$this->data->id;
                    $id_field = $this->hidden('id');
                }
                //  if we dont wnat it in url, remove it
                if (array_search('disable_url_id', $opt) !== false) {
                    $url_id = '';
                }
                $url = $url.$url_id;
            }
        }

        //  form class
        $class = 'form-horizontal';
        if (array_key_exists('class', $opt)) {
            $class = $opt['class'];
        }

        //  do we want csrf ?
        if (array_search('disable_csrf', $opt) === false) {
            $csrf_field = $this->csrf();
        } else {
            $csrf_field = '';
        }

        $html = '<form action="'.Router::url($url).'" class="'.$class.'" method="'.$method.'" role="form">'.$csrf_field.$id_field;

        return $html;
    }

    /**
    *   close
    *
    **/
    public function close()
    {
        return '</form>';
    }

    /**
    *   button
    *
    **/
    public function button($name, $opt = [])
    {
        $label = $this->label($name, $opt, true);

        $class = 'default';
        if (isset($opt['class'])) {
            $class = $opt['class'];
        }

        $type = 'link';
        if (isset($opt['type'])) {
            $type = $opt['type'];
        }

        $submit = '';
        if (array_search('submit', $opt) !== false) {
            $submit = ' type="submit" ';
            $type   = 'button';
            if (!isset($opt['class'])) {
                $class = 'primary';
            }
        }

        $link = '';
        if (isset($opt['url'])) {
            $link = ' href="'.Router::url($opt['url']).'" ';
        }

        switch ($type) {
            case 'button':
                $html = '<button '.$submit.' class="btn btn-'.$class.'">'.$label.'</button>';
                break;

            case 'link':
                $html = '<a '.$link.' class="btn btn-'.$class.'" id="btn_'.$name.'">'.$label.'</a>';
                break;

            default:
                $html = '<'.$type.' '.$link.' class="btn btn-'.$class.'" id="btn_'.$name.'">'.$label.'</'.$type.'>';
                break;
        }

        return $html;
    }


    /**
    *   input
    *
    **/
    protected function input($type, $name, $opt = [])
    {
        $label = $this->label($name, $opt);

        $value = '';
        if ($type != 'password') {
            if ($this->data->$name) {
                $value = $this->data->$name;
            }
        }

        $class = '';
        if (array_key_exists('class', $opt)) {
            $class = $opt['class'];
        }

        $addon = '';
        if (array_key_exists('addon', $opt)) {
            $addon = '<div class="input-group-addon">'.$opt['addon'].'</div>';
        }

        $placeholder = $this->placeholder($name, $opt);

        $error = '';
        if ($this->errors->$name) {
            $error = $this->translate->$name->error->{$this->errors->$name};
        }

        $required = '';
        if ((isset($opt['required']) && $opt['required'] == true) || (array_search('required', $opt) !== false)) {
            $required = ' required ';
        }

        $html = '<input type="'.$type.'" class="form-control" name="'.$name.'" id="input_'.$name.'"'.($value != '' ? ' value="'.$value.'"' : '').$placeholder.$required.'">';

        if ($addon != '') {
            $html = '<div class="input-group">'.$addon.$html.'</div>';
        }

        return $this->design($html, $error, $label, $name, $class);
    }

    /**
    *   design
    *
    **/
    protected function design($field, $error, $label, $name, $class = '')
    {
        $html = '<div class="form-group'.($error != '' ? ' has-error' : '').' '.$class.'">';
        $html .= '<label for="input_'.$name.'" class="col-sm-2 control-label">'.$label.'</label>';
        $html .= '<div class="col-sm-10">';
        $html .= $field;
        $html .= '<span class="help-block">'.$error.'</span>';
        $html .= '</div></div>';

        return $html;
    }

    /**
    *   label
    *
    **/
    protected function label($name, $opt, $button = false)
    {
        $label = '';
        if ($button) {
            $icon_name = false;
            if (array_key_exists('label', $opt)) {
                $label = $opt['label'];
                if (array_key_exists('icon', $opt)) {
                    $icon_name = $opt['icon'];
                }
            } else {
                $label = $this->btn->$name;
                if ($this->btn->{$name.'_icon'}) {
                    $icon_name = $this->btn->{$name.'_icon'};
                }
            }

            //  if icon is specified , add it !
            if ($icon_name) {
                $label = '<span class="glyphicon glyphicon-'.$icon_name.'"></span> '.$label;
            }
        } else {
            if (array_key_exists('label', $opt)) {
                $label = $opt['label'];
            } else {
                $label = $this->translate->$name->label;
            }
        }

        return $label;
    }

    /**
    *   placeholder
    *
    **/
    protected function placeholder($name, $opt)
    {
        $placeholder = '';

        if (array_key_exists('placeholder', $opt)) {
            $placeholder = $opt['placeholder'];
        } elseif ($this->translate->$name->placeholder) {
            $placeholder = $this->translate->$name->placeholder;
        }

        if ($placeholder != '') {
            $placeholder = ' placeholder="'.$placeholder.'" ';
        }

        return $placeholder;
    }


    /**
    *   text
    *
    **/
    public function text($name, $opt = [])
    {
        return $this->input('text', $name, $opt);
    }

    /**
    *   email
    *
    **/
    public function email($name = 'email', $opt = '')
    {
        return $this->input('email', $name, $opt);
    }

    /**
    *   password
    *
    **/
    public function password($name, $opt = [])
    {
        return $this->input('password', $name, $opt);
    }

    /**
    *   select
    *
    **/
    public function select($name, $values, $opt = [])
    {
        if (!is_array($values)) {
            throw new SLException("Error parsing select values", 500);
        }

        //  label
        $label = $this->label($name, $opt);

        $current_value = '';
        if ($this->data->$name) {
            $current_value = $this->data->$name;
        }

        $error = '';
        if (isset($this->errors->$name)) {
            $error = $this->errors->$name;
        }

        $html = '<select name="'.$name.'" id="input_'.$name.'" class="form-control">';

        foreach ($values as $val => $disp) {
            $html .= '<option value="'.$val.'"'.($val == $current_value ? ' selected="selected" ' : '').'>'.$disp.'</option>';
        }

        $html .= '</select>';

        return $this->design($html, $error, $label, $name);
    }

    /**
    *   radio
    *
    **/
    public function radio($name, $data, $opt = [])
    {
        $field = '';
        $i = 1;

        $label = '';
        if (array_key_exists('label', $opt)) {
            $label = $opt['label'];
        } else {
            $label = $this->translate->$name->label;
        }

        $error = '';
        if ($this->errors->$name) {
            $error = $this->translate->$name->error->{$this->errors->$name};
        }

        foreach ($data as $item) {
            $checked = '';
            if ($this->data->$name == $item) {
                $checked = ' checked ';
            }
            $field .= '<label class="radio-inline"><input type="radio" name="'.$name.'" id="input_'.$name.'_'.$i.'" value="'.$item.'" '.$checked.'> '.$this->translate->$name->$item->full.'</label> ';
            $i++;
        }

        return $this->design($field, $error, $label, $name);
    }

    /**
    *   checkbox
    *
    **/
    public function checkbox($name)
    {
        $checked = '';
        if ($this->data->$name) {
            $checked = ' checked ';
        }

        $label = $this->translate->$name->label;

        $error = '';
        if ($this->errors->$name) {
            $error = $this->translate->$name->error->{$this->errors->$name};
        }

        $field = $this->hidden($name, 0);
        $item_label = '';
        if ($this->translate->$name->item) {
            $item_label = $this->translate->$name->item;
        }
        $field .= '<label class="radio-inline"><input type="checkbox" name="'.$name.'" id="input_'.$name.'" value="1" '.$checked.'>'.$item_label.' </label> ';
        return $this->design($field, $error, $label, $name);
    }

    /**
    *   hidden
    *
    **/
    public function hidden($name, $value = '')
    {
        if ($value === '') {
            $value = $this->data->$name;
        }

        $html = '<input type="hidden" name="'.$name.'" value="'.$value.'">';

        return $html;
    }

    /**
    *   textarea
    *
    **/
    public function textarea($name, $opt = [])
    {
        $label = $this->label($name, $opt);

        $content = $this->data->$name === false ? '' : $this->data->$name;

        $cols = array_key_exists('cols', $opt) ? intval($opt['cols']) : 30;
        $rows = array_key_exists('rows', $opt) ? intval($opt['rows']) : 10;

        $required = '';
        if (array_search('required', $opt) !== false || (array_key_exists('required', $opt) !== false && $opt['required'] != false)) {
            $required = 'required';
        }

        $error = '';
        if ($this->errors->$name) {
            $error = $this->translate->$name->error->{$this->errors->$name};
        }

        $field = '<textarea name="'.$name.'" id="input_'.$name.'" cols="'.$cols.'" rows="'.$rows.'" class="form-control"'.$required.'>'.$content.'</textarea>';

        return $this->design($field, $error, $label, $name);
    }


    /**
    *   csrf
    *
    **/
    public function csrf()
    {
        return $this->hidden('csrf', $this->session_csrf);
    }


}