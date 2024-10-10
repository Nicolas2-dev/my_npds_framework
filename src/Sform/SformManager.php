<?php

namespace Npds\Sform;

use Npds\Http\Request;

/**
 * Undocumented class
 */
class SformManager
{

    /**
     * 
     */
    const CRLF = "\n";

    /**
     * Undocumented variable
     *
     * @var array
     */
    public  $form_fields = array(); 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $title; 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $mess; 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $form_title; 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $form_id;  

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $form_method; 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $form_key; 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $form_key_value; 

    /**
     * Undocumented variable
     *
     * @var string
     */
    public  $form_key_status = 'open'; 

    /**
     * Undocumented variable
     *
     * @var string
     */
    public  $submit_value = ''; 

    /**
     * Undocumented variable
     *
     * @var string
     */
    public  $form_password_access = ''; 

    /**
     * Undocumented variable
     *
     * @var array
     */
    public  $answer = array(); 

    /**
     * Undocumented variable
     *
     * @var string
     */
    public  $form_check = 'true'; 

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    public  $url; 

    /**
     * Undocumented variable
     *
     * @var integer
     */
    public  $field_size = 50; 

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $form_fileds_globals = [];

    /**
     * Undocumented function
     *
     * @param [type] $ibid
     * @return void
     */
    public function interro_fields($ibid)
    {
        $number = "no";

        for (Reset($this->form_fields), $node = 0; $node < count($this->form_fields); Next($this->form_fields), $node++) {
            if (array_key_exists('name', $this->form_fields[$node])) {
                if ($ibid == $this->form_fields[$node]['name']) {
                    $number = $node;
                    break;
                }
            }
        }

        return $number;
    }

    /**
     * Undocumented function
     *
     * @param [type] $ibid0
     * @param [type] $ibid1
     * @return void
     */
    public function interro_array($ibid0, $ibid1)
    {
        $number = 'no';

        foreach ($ibid0 as $key => $val) {
            if ($ibid1 == $val['en']) {
                $number = $key;
                break;
            }
        }

        return $number;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_form_fields_globals($form_globals)
    {
        $this->form_fileds_globals = array_merge($this->form_fileds_globals, $form_globals);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function get_form_fields_globals()
    {
        return $this->form_fileds_globals;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_form_field_size($en)
    {
        $this->field_size = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_form_title($en)
    {
        $this->form_title = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_form_id($en)
    {
        $this->form_id = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_form_method($en)
    {
        $this->form_method = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_form_check($en)
    {
        $this->form_check = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_url($en)
    {
        $this->url = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_key($en)
    {
        $this->form_key = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_submit_value($en)
    {
        $this->submit_value = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function key_lock($en)
    {
        if ($en == 'open') {
            $this->form_key_status = 'open';
        } else {
            $this->form_key_status = 'close';
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_mess($en)
    {
        $this->mess = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $en
     * @param string $value
     * @param string $type
     * @param boolean $obligation
     * @param string $size
     * @param string $diviseur
     * @param string $ctrl
     * @return void
     */
    public function add_field($name, $en, $value = '', $type = 'text', $obligation = false, $size = '50', $diviseur = '5', $ctrl = '')
    {
        if ($type == 'submit') {
            $name = $this->submit_value;
        }

        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name,
            'type' => $type,
            'en' => $en,
            'value' => $value,
            'size' => $size,
            'diviseur' => $diviseur,
            'obligation' => $obligation,
            'ctrl' => $ctrl
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $en
     * @param string $value
     * @param boolean $obligation
     * @param boolean $checked
     * @return void
     */
    public function add_checkbox($name, $en, $value = '', $obligation = false, $checked = false)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name,
            'en' => $en,
            'value' => $value,
            'type' => "checkbox",
            'checked' => $checked,
            'obligation' => $obligation
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $en
     * @param [type] $values
     * @param boolean $obligation
     * @param integer $size
     * @param boolean $multiple
     * @return void
     */
    public function add_select($name, $en, $values, $obligation = false, $size = 1, $multiple = false)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name,
            'en' => $en,
            'type' => "select",
            'value' => $values,
            'size' => $size,
            'multiple' => $multiple,
            'obligation' => $obligation
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $en
     * @param [type] $values
     * @param boolean $obligation
     * @return void
     */
    public function add_radio($name, $en, $values, $obligation = false)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name,
            'en' => $en,
            'type' => "radio",
            'value' => $values,
            'obligation' => $obligation
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $en
     * @param [type] $value
     * @param string $type
     * @param string $modele
     * @param boolean $obligation
     * @param string $size
     * @return void
     */
    public function add_date($name, $en, $value, $type = 'date', $modele = 'm/d/Y', $obligation = false, $size = '10')
    {
        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name,
            'type' => $type,
            'model' => $modele,
            'en' => $en,
            'value' => $value,
            'size' => $size,
            'obligation' => $obligation,
            'ctrl' => 'date'
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_title($en)
    {
        $this->title = $en;
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_comment($en)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'en' => $en,
            'type' => "comment"
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_extra($en)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'en' => $en,
            'type' => "extra"
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $en
     * @return void
     */
    public function add_extra_hidden($en)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'en' => $en,
            'type' => "extra-hidden"
        );
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function add_Qspam()
    {
        $this->form_fields[count($this->form_fields)] = array(
            'en' => "",
            'type' => "Qspam"
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $javas
     * @param [type] $html
     * @return void
     */
    public function add_extender($name, $javas, $html)
    {
        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name . "extender",
            'javas' => $javas,
            'html' => $html
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $en
     * @param string $size
     * @param string $file_size
     * @return void
     */
    public function add_upload($name, $en, $size = '50', $file_size = '')
    {
        $this->form_fields[count($this->form_fields)] = array(
            'name' => $name,
            'en' => $en,
            'value' => "",
            'type' => "upload",
            'size' => $size,
            'file_size' => $file_size
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $bg
     * @return void
     */
    public function print_form($bg, $retour = '')
    {
        if (isset($this->form_id)) {
            $id_form = 'id="' . $this->form_id . '"';
        } else {
            $id_form = '';
        }

        $str = '';

        if ($this->form_method != '') {
            $str .= "\n<form action=\"" . $this->url . "\" " . $id_form . "  method=\"" . $this->form_method . "\" name=\"" . $this->form_title . "\" enctype=\"multipart/form-data\"";
            
            if ($this->form_check == 'true') {
                $str .= ' onsubmit="return check();">';
            } else {
                $str .= '>';
            }
        }

        // todo utilisation de tabindex dans les input
        $str .= '
        <fieldset>
            <div class="mb-4">' . $this->title . '</div>';

        for ($i = 0; $i < count($this->form_fields); $i++) {
            if (array_key_exists('size', $this->form_fields[$i])) {
                
                if ($this->form_fields[$i]['size'] >= $this->field_size) {
                    $csize = $this->field_size;
                } else {
                    $csize = (int)$this->form_fields[$i]['size'] + 1;
                }
            }

            if (array_key_exists('name', $this->form_fields[$i])) {
                $num_extender = $this->interro_fields($this->form_fields[$i]['name'] . 'extender');
            } else {
                $num_extender = 'no';
            }

            if (array_key_exists('type', $this->form_fields[$i])) {
                switch ($this->form_fields[$i]['type']) {
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'number':
                        $str .= '
                        <div class="mb-3 row">
                        <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'];

                        $this->form_fields[$i]['value'] = str_replace('\'', '&#039;', $this->form_fields[$i]['value']);

                        $requi = '';

                        if ($this->form_fields[$i]['obligation']) {
                            $requi = 'required="required"';
                            $this->form_check .= " && (f.elements['" . $this->form_fields[$i]['name'] . "'].value!='')";
                            $str .= '<span class="text-danger ms-2">*</span>';
                        }

                        $str .= '</label>
                        <div class="col-sm-8">';

                        // Charge la valeur et analyse la clef
                        if ($this->form_fields[$i]['name'] == $this->form_key) {
                            $this->form_key_value = $this->form_fields[$i]['value'];

                            if ($this->form_key_status == 'close') {
                                $str .= '<input class="form-control" readonly="readonly" type="' . $this->form_fields[$i]['type'] . '" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" ';
                            } else {
                                $str .= '<input class="form-control" type="' . $this->form_fields[$i]['type'] . '" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" ' . $requi;
                            }
                        } else {
                            $str .= '<input class="form-control" type="' . $this->form_fields[$i]['type'] . '" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" ' . $requi;
                        }

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['javas'] . '>';
                            $str .= $this->form_fields[$num_extender]['html'];
                        } else {
                            $str .= ' /> ';
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                    case 'password-access':
                        $this->form_fields[$i]['value'] = $this->form_password_access;

                    case 'password':
                        $str .= '
                        <div class="mb-3 row">
                        <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'];

                        $this->form_fields[$i]['value'] = str_replace('\'', '&#039;', $this->form_fields[$i]['value']);

                        $requi = '';
                        if ($this->form_fields[$i]['obligation']) {
                            $requi = 'required="required"';

                            $this->form_check .= " && (f.elements['" . $this->form_fields[$i]['name'] . "'].value!='')";

                            $str .= '&nbsp;<span class="text-danger">*</span></label>';
                        } else {
                            $str .= '</label>';
                        }

                        $str .= '
                        <div class="col-sm-8">
                            <input class="form-control" type="' . $this->form_fields[$i]['type'] . '" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" ' . $requi . ' />';
                        
                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                    case 'checkbox':
                        $requi = '';

                        if ($this->form_fields[$i]['obligation']) {
                            $requi = 'required="required"';
                        }

                        $str .= '
                        <div class="mb-3 row">
                        <div class="col-sm-8 ms-sm-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" ' . $requi;
                        
                        $str .= ($this->form_fields[$i]['checked']) ? ' checked="checked" />' : ' />';
                        $str .= '<label class="form-check-label" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'];

                        if ($this->form_fields[$i]['obligation']) {
                            $str .= '<span class="text-danger"> *</span>';
                        }

                        $str .= '</label>
                        </div>';

                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                    case 'textarea':
                        $requi = '';

                        if ($this->form_fields[$i]['obligation']) {
                            $requi = 'required="required"';
                        }

                        $str .= '
                        <div class="mb-3 row">
                        <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'];

                        $this->form_fields[$i]['value'] = str_replace('\'', '&#039;', $this->form_fields[$i]['value']);

                        if ($this->form_fields[$i]['obligation']) {
                            $this->form_check .= " && (f.elements['" . $this->form_fields[$i]['name'] . "'].value!='')";
                            $str .= '&nbsp;<span class="text-danger">*</span>';
                        }

                        $str .= '</label>';
                        $txt_row = $this->form_fields[$i]['diviseur'];

                        //$txt_col=( ($this->form_fields[$i]['size'] - ($this->form_fields[$i]['size'] % $txt_row)) / $txt_row);

                        $str .= '
                        <div class="col-sm-8">
                            <textarea class="form-control" name="' . $this->form_fields[$i]['name'] . '" id="' . $this->form_fields[$i]['name'] . '" rows="' . $txt_row . '" ' . $requi . '>' . $this->form_fields[$i]['value'] . '</textarea>';
                        
                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                        //not sure to check if ok on all case
                    case 'show-hidden':
                        $str .= '
                        <div class="mb-3 row">
                        <label class="col-form-label col-sm-4">' . $this->form_fields[$i]['en'] . '</label>
                        <div class="col-sm-8">';

                        if ($num_extender != "no") {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';

                    case 'hidden':
                        $this->form_fields[$i]['value'] = str_replace('\'', '&#039;', $this->form_fields[$i]['value']);
                        $str .= '<input type="hidden" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" />';
                        break;

                    case 'select':
                        $str .= '
                        <div class="mb-3 row">
                            <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'] . '</label>
                            <div class="col-sm-8">
                            <select class="';

                        $str .= ($this->form_fields[$i]['multiple']) ? 'form-control' : 'form-select';
                        $str .= '" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'];
                        $str .= ($this->form_fields[$i]['multiple']) ? '[]" multiple="multiple"' : "\"";

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['javas'] . ' ';
                        }

                        $str .= ($this->form_fields[$i]['size'] > 1) ? " size=\"" . $this->form_fields[$i]['size'] . "\">" : '>';

                        foreach ($this->form_fields[$i]['value'] as $key => $val) {
                            $str .= '<option value="' . $key . '"';

                            if (array_key_exists('selected', $val) and $val['selected']) {
                                $str .= ' selected="selected" >';
                            } else {
                                $str .= ' >';
                            }

                            $str .= str_replace('\'', '&#039;', $val['en'] ?: '') . '</option>';
                        }

                        $str .= '</select>';

                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                    case 'radio':
                        $first_radio = true;

                        $str .= '
                        <div class="mb-3 row">
                        <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'] . '</label>
                        <div class="col-sm-8">';

                        foreach ($this->form_fields[$i]['value'] as $key => $val) {
                            $str .= '<input class="" type="radio" ';

                            if ($first_radio) {
                                $str .= 'id="' . $this->form_fields[$i]['name'] . '" ';
                                $first_radio = false;
                            }

                            $str .= 'name="' . $this->form_fields[$i]['name'] . '" value="' . $key . '"';
                            $str .= ($val['checked']) ? ' checked="checked" />&nbsp;' : ' />&nbsp;';

                            $str .= $val['en'] . '&nbsp;&nbsp;';
                        }

                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                    case 'comment':
                        $str .= '
                        <div class="col-sm-12">
                            <p>' . $this->form_fields[$i]['en'] . '</p>
                        </div>';
                        break;

                    case 'Qspam':
                        $str .= Q_spambot();
                        $str .= "\n";
                        break;

                    case 'extra':
                    case 'extra-hidden':
                        $str .= $this->form_fields[$i]['en'];
                        break;

                    case 'submit':
                        $this->form_fields[$i]['value'] = str_replace('\'', '&#039;', $this->form_fields[$i]['value']);
                        $str .= '<button class="btn btn-primary" id="' . $this->form_fields[$i]['name'] . '" type="submit" name="' . $this->form_fields[$i]['name'] . '" >' . $this->form_fields[$i]['value'] . '</button>';
                        break;

                    case 'reset':
                        $this->form_fields[$i]['value'] = str_replace('\'', '&#039;', $this->form_fields[$i]['value']);
                        $str .= $this->form_fields[$i]['en'];
                        $str .= '<input class="btn btn-secondary" id="' . $this->form_fields[$i]['name'] . '" type="reset" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" />';
                        break;

                    case 'stamp':
                        if ($this->form_fields[$i]['value'] == '') {
                            $this->form_fields[$i]['value'] = strtotime("now");
                        }

                        if ($this->form_fields[$i]['name'] == $this->form_key) {
                            $this->form_key_value = $this->form_fields[$i]['value'];
                        }

                        $str .= '<input type="hidden" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" />';
                        break;

                    case 'date':
                        if ($this->form_fields[$i]['value'] == '') {
                            $this->form_fields[$i]['value'] = date($this->form_fields[$i]['model']);
                        }

                        $str .= '
                        <div class="mb-3 row">
                        <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'];

                        if ($this->form_fields[$i]['obligation']) {
                            $this->form_check .= " && (f.elements['" . $this->form_fields[$i]['name'] . "'].value!='')";

                            $str .= '&nbsp;<span class="text-danger">*</span></label>';
                        } else {
                            $str .= '</label>';
                        }

                        if ($this->form_fields[$i]['name'] == $this->form_key) {
                            $this->form_key_value = $this->form_fields[$i]['value'];

                            if ($this->form_key_status == 'close'){
                                $str .= '
                                <input type="hidden" id="' . $this->form_fields[$i]['name'] . '" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" />
                                <b>' . $this->form_fields[$i]['value'] . '</b>';
                            } else {
                                $str .= '
                                <div class="col-sm-8">
                                    <input class="form-control" id="' . $this->form_fields[$i]['name'] . '" type="text" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" />';
                            }
                        } else {
                            $str .= '
                            <div class="col-sm-8">
                                <input class="form-control" id="' . $this->form_fields[$i]['name'] . '" type="text" name="' . $this->form_fields[$i]['name'] . '" value="' . $this->form_fields[$i]['value'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" />';
                        }

                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;

                    case 'upload':
                        $str .= '
                        <div id="avava" class="mb-3 row" lang="' . language_iso(1, '', '') . '">
                        <label class="col-form-label col-sm-4" for="' . $this->form_fields[$i]['name'] . '">' . $this->form_fields[$i]['en'] . '</label>
                        <div class="col-sm-8">
                            <div class="input-group mb-2 me-sm-2">
                                <button class="btn btn-secondary" type="button" onclick="reset2($(\'#' . $this->form_fields[$i]['name'] . '\'),\'\');"><i class="fas fa-sync"></i></button>
                                <label class="input-group-text n-ci" id="lab" for="' . $this->form_fields[$i]['name'] . '"></label>
                                <input type="file" class="form-control custom-file-input" id="' . $this->form_fields[$i]['name'] . '"  name="' . $this->form_fields[$i]['name'] . '" size="' . $csize . '" maxlength="' . $this->form_fields[$i]['size'] . '" />
                            </div>
                            <input type="hidden" name="MAX_FILE_SIZE" value="' . $this->form_fields[$i]['file_size'] . '" />';

                        if ($num_extender != 'no') {
                            $str .= $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '
                        </div>
                        </div>';
                        break;
                    default:
                        break;
                }
            }
        }

        $str .= '</fieldset>';

        if ($this->form_method != '') {
            $str .= '</form>';
        }

        // cette condition n'est pas fonctionnelle
        if ($this->form_check != 'false') { 
            $str .= "<script type=\"text/javascript\">//<![CDATA[" . static::CRLF;
            $str .= "var f=document.forms['" . $this->form_title . "'];" . static::CRLF;
            $str .= "function check(){" . static::CRLF;
            $str .= " if(" . $this->form_check . "){" . static::CRLF;
            $str .= "   f.submit();" . static::CRLF;
            $str .= "   return true;" . static::CRLF;
            $str .= " } else {" . static::CRLF;
            $str .= "   alert('" . $this->mess . "');" . static::CRLF;
            $str .= "   return false;" . static::CRLF;
            $str .= "}}" . static::CRLF;
            $str .= "//]]></script>\n";
        }

        if ($retour != 'not_echo') {
            echo $str;
        } else {
            return $str;
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function print_form_hidden()
    {
        $str = '';

        for ($i = 0; $i < count($this->form_fields); $i++) {
            if (array_key_exists('name', $this->form_fields[$i])) {

                $str .= '<input type="hidden" name="' . $this->form_fields[$i]['name'] . '" value="';

                if (array_key_exists('value', $this->form_fields[$i])) {
                    $str .= stripslashes(str_replace('\'', '&#039;', $this->form_fields[$i]['value'])) . '"';
                } else {
                    $str .= '"';
                }

                $str .= ' />';
            }
        }

        return $str;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function make_response()
    {
        for ($i = 0; $i < count($this->form_fields); $i++) {
            $this->answer[$i] = '';

            if (array_key_exists('type', $this->form_fields[$i])) {
                switch ($this->form_fields[$i]['type']) {
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'number':
                        // Charge la valeur de la clef
                        if ($this->form_fields[$i]['name'] == $this->form_key) {
                            $this->form_key_value = $GLOBALS[$this->form_fields[$i]['name']];
                        }

                    case 'password':
                        if ($this->form_fields[$i]['ctrl'] != "") {
                            $this->control($this->form_fields[$i]['name'], $this->form_fields[$i]['en'], $GLOBALS[$this->form_fields[$i]['name']], $this->form_fields[$i]['ctrl']);
                        }

                        $this->answer[$i] .= "<TEXT>\n";
                        $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $GLOBALS[$this->form_fields[$i]['name']] . "</" . $this->form_fields[$i]['name'] . ">\n";
                        $this->answer[$i] .= "</TEXT>";
                        break;

                    case 'password-access':
                        if ($this->form_fields[$i]['ctrl'] != "") {
                            $this->control($this->form_fields[$i]['name'], $this->form_fields[$i]['en'], $GLOBALS[$this->form_fields[$i]['name']], $this->form_fields[$i]['ctrl']);
                        }

                        $this->form_password_access = $GLOBALS[$this->form_fields[$i]['name']];
                        break;

                    case 'textarea':
                    case 'textarea_no_mceEditor':
                        $this->answer[$i] .= "<TEXT>\n";
                        $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . str_replace(chr(13) . chr(10), "&lt;br /&gt;", $GLOBALS[$this->form_fields[$i]['name']]) . "</" . $this->form_fields[$i]['name'] . ">\n";
                        $this->answer[$i] .= "</TEXT>";
                        break;

                    case 'select':
                        $this->answer[$i] .= "<SELECT>\n";

                        if (is_array($GLOBALS[$this->form_fields[$i]['name']])) {
                            for ($j = 0; $j < count($GLOBALS[$this->form_fields[$i]['name']]); $j++) {
                                $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $this->form_fields[$i]['value'][$GLOBALS[$this->form_fields[$i]['name']][$j]]['en'] . "</" . $this->form_fields[$i]['name'] . ">\n";
                            }
                        } else {
                            $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $this->form_fields[$i]['value'][$GLOBALS[$this->form_fields[$i]['name']]]['en'] . "</" . $this->form_fields[$i]['name'] . ">";
                        }

                        $this->answer[$i] .= "</SELECT>";
                        break;

                    case 'radio':
                        $this->answer[$i] .= "<RADIO>\n";
                        $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $this->form_fields[$i]['value'][$GLOBALS[$this->form_fields[$i]['name']]]['en'] . "</" . $this->form_fields[$i]['name'] . ">\n";
                        $this->answer[$i] .= "</RADIO>";
                        break;

                    case 'checkbox':
                        $this->answer[$i] .= "<CHECK>\n";

                        if ($GLOBALS[$this->form_fields[$i]['name']] != "") {
                            $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $this->form_fields[$i]['value'] . "</" . $this->form_fields[$i]['name'] . ">\n";
                        } else {
                            $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . "></" . $this->form_fields[$i]['name'] . ">\n";
                        }

                        $this->answer[$i] .= "</CHECK>";
                        break;

                    case 'date':
                        if ($this->form_fields[$i]['ctrl'] != "") {
                            $this->control($this->form_fields[$i]['name'], $this->form_fields[$i]['en'], $GLOBALS[$this->form_fields[$i]['name']], $this->form_fields[$i]['ctrl']);
                        }

                        if ($this->form_fields[$i]['name'] == $this->form_key) {
                            $this->form_key_value = $GLOBALS[$this->form_fields[$i]['name']];
                        }

                        $this->answer[$i] .= "<DATUM>\n";
                        $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $GLOBALS[$this->form_fields[$i]['name']] . "</" . $this->form_fields[$i]['name'] . ">\n";
                        $this->answer[$i] .= "</DATUM>";
                        break;

                    case 'stamp':
                        if ($this->form_fields[$i]['name'] == $this->form_key) {
                            $this->form_key_value = $GLOBALS[$this->form_fields[$i]['name']];
                        }

                        $this->answer[$i] .= "<TIMESTAMP>\n";
                        $this->answer[$i] .= "<" . $this->form_fields[$i]['name'] . ">" . $GLOBALS[$this->form_fields[$i]['name']] . "</" . $this->form_fields[$i]['name'] . ">\n";
                        $this->answer[$i] .= "</TIMESTAMP>";
                        break;

                    case 'hidden':
                    case 'submit':
                    case 'reset':
                    default:
                        $this->answer[$i] .= "no_reg";
                        break;
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $response
     * @return void
     */
    public function write_sform_data($response)
    {
        $content = "<CONTENTS>\n";

        for (Reset($response), $node = 0; $node < count($response); Next($response), $node++) {
            if ($response[$node] != "no_reg") {
                $content .= $response[$node] . "\n";
            }
        }

        $content .= "</CONTENTS>";

        return addslashes($content);
    }

    /**
     * Undocumented function
     *
     * @param [type] $line
     * @param [type] $op
     * @return void
     */
    public function read_load_sform_data($line, $op)
    {
        if ((!stristr($line, "<CONTENTS>")) and (!stristr($line, "</CONTENTS>"))) {
            // Premier tag
            $nom = substr($line, 1, strpos($line, ">") - 1);

            // jusqu'a </xxx
            $valeur = substr($line, strpos($line, ">") + 1, (strpos($line, "<", 1) - strlen($nom) - 2));

            if ($valeur == "") {
                $op = $nom;
            }

            switch ($op) {
                case "TEXT":
                    $op = "TEXT_S";
                    break;

                case "TEXT_S":
                    $num = $this->interro_fields($nom);

                    if ($num != "no" or $num == "0") {
                        $valeur = str_replace("&lt;BR /&gt;", chr(13) . chr(10), $valeur);
                        $valeur = str_replace("&lt;br /&gt;", chr(13) . chr(10), $valeur);

                        $this->form_fields[$num]['value'] = $valeur;
                    }
                    break;

                case "/TEXT":
                    break;

                case "SELECT":
                    $op = "SELECT_S";
                    break;

                case "SELECT_S":
                    $num = $this->interro_fields($nom);

                    if ($num != "no" or $num == "0") {
                        $tmp = $this->interro_array($this->form_fields[$num]['value'], $valeur);

                        $this->form_fields[$num]['value'][$tmp]['selected'] = true;
                    }
                    break;

                case "/SELECT":
                    break;

                case "RADIO":
                    $op = "RADIO_S";
                    break;

                case "RADIO_S":
                    $num = $this->interro_fields($nom);

                    if ($num != "no" or $num == "0") {
                        $tmp = $this->interro_array($this->form_fields[$num]['value'], $valeur);

                        $this->form_fields[$num]['value'][$tmp]['checked'] = true;
                    }
                    break;

                case "/RADIO":
                    break;

                case "CHECK":
                    $op = "CHECK_S";
                    break;

                case "CHECK_S":
                    $num = $this->interro_fields($nom);

                    if ($num != "no" or $num == "0") {
                        if ($valeur) {
                            $valeur = true;
                        } else {
                            $valeur = false;
                        }

                        $this->form_fields[$num]['checked'] = $valeur;
                    }
                    break;

                case "/CHECK":
                    break;

                case "TIMESTAMP":
                case "DATUM":
                    $op = "DATUM_S";
                    break;

                case "DATUM_S":
                    $num = $this->interro_fields($nom);

                    if ($num != "no" or $num == "0") {
                        $this->form_fields[$num]['value'] = $valeur;
                    }
                    break;

                case "/DATUM":
                    break;

                default:
                    break;
            }
        }

        return $op;
    }

    /**
     * Undocumented function
     *
     * @param [type] $bg
     * @param string $retour
     * @param string $action
     * @return void
     */
    public function aff_response($bg, $retour = '', $action = '')
    {
        // modif Field en lieu et place des $GLOBALS ....
        settype($str, 'string');

        for ($i = 0; $i < count($this->form_fields); $i++) {
            if (array_key_exists('name', $this->form_fields[$i])) {

                $num_extender = $this->interro_fields($this->form_fields[$i]['name'] . "extender");

                //$this->form_fileds_globals = array_flip($this->form_fileds_globals);

//vd('greee', $this->form_fileds_globals);


                // if (array_key_exists($this->form_fields[$i]['name'], $GLOBALS)){
                //     $field = $GLOBALS[$this->form_fields[$i]['name']];

                // } 
                // else
                if (array_key_exists($this->form_fields[$i]['name'], $this->form_fileds_globals)) {
                    $field = $this->form_fileds_globals[$this->form_fields[$i]['name']];

                } 
                // elseif (array_key_exists($this->form_fields[$i]['name'], Request::post())) {

                //     $field = Request::post($this->form_fields[$i]['name']);
                // } 
                else {
                    $field = '';
                }

//vd($GLOBALS, $this->form_fields[$i]['name'], $field, $this->form_fields[$i]);

            } else {
                $num_extender = 'no';
            }

            if (array_key_exists('type', $this->form_fields[$i])) {
                switch ($this->form_fields[$i]['type']) {
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'number':
                        $str .= '<p class="mb-1">' . $this->form_fields[$i]['en'];
                        $str .= '<br />';
                        $str .= '<strong>' . stripslashes($field) . '&nbsp;</strong>';

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }

                        $str .= '</p>';
                        break;

                    case 'password':
                        $str .= '<br />' . $this->form_fields[$i]['en'];
                        $str .= '&nbsp;<strong>' . str_repeat("*", strlen($field)) . '&nbsp;</strong>';

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }
                        break;

                    case 'checkbox':
                        $str .= '<br />' . $this->form_fields[$i]['en'];

                        if ($field != '') {
                            $str .= '&nbsp;<strong>' . $this->form_fields[$i]['value'] . '&nbsp;</strong>';
                        }

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }
                        break;

                    case 'textarea':
                        $str .= '<br />' . $this->form_fields[$i]['en'];
                        $str .= '<br /><strong>' . stripslashes(str_replace(chr(13) . chr(10), '<br />', $field)) . '&nbsp;</strong>';

                        if ($num_extender != 'no')  {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }
                        break;

                    case 'select':
                        $str .= '<br />' . $this->form_fields[$i]['en'];

                        if (is_array($field)) {
                            for ($j = 0; $j < count($field); $j++) {
                                $str .= '<strong>' . $this->form_fields[$i]['value'][$field[$j]]['en'] . '&nbsp;</strong><br />';
                            }
                        } else {
                            $str .= '&nbsp;<strong>' . $this->form_fields[$i]['value'][$field]['en'] . '&nbsp;</strong>';
                        }

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }
                        break;

                    case 'radio':
                        $str .= '<br />' . $this->form_fields[$i]['en'];
                        $str .= '&nbsp;<strong>' . $this->form_fields[$i]['value'][$field]['en'] . '&nbsp;</strong>';

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }
                        break;

                    case 'comment':
                        $str .= '<br />';
                        $str .= $this->form_fields[$i]['en'];
                        break;

                    case 'extra':
                        $str .= $this->form_fields[$i]['en'];
                        break;

                    case 'date':
                        $str .= '<br />' . $this->form_fields[$i]['en'];
                        $str .= '&nbsp;<strong>' . $field . '&nbsp;</strong>';

                        if ($num_extender != 'no') {
                            $str .= ' ' . $this->form_fields[$num_extender]['html'];
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        if (($retour != '') and ($retour != 'not_echo')) {
            $str .= '<a href="' . $action . '" class="btn btn-secondary">[ ' . $retour . ' ]</a>';
        }

        $str .= '';

        if ($retour != 'not_echo') {
            echo $str;
        } else {
            return $str;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $nom
     * @param [type] $valeur
     * @param [type] $controle
     * @return void
     */
    public function control($name, $nom, $valeur, $controle)
    {

        $i = $this->interro_fields($name);

        if (($this->form_fields[$i]['obligation'] != true) and ($valeur == "")) {
            $controle = '';
        }

        switch ($controle) {
            case 'a-9':
                if (preg_match_all("/([^a-zA-Z0-9 ])/i", $valeur, $trouve)) {
                    $this->error($nom, implode(" ", $trouve[0]));
                    exit();
                }
                break;

            case 'A-9':
                if (preg_match_all("([^A-Z0-9 ])", $valeur, $trouve)) {
                    return (false);
                    exit();
                }
                break;

            case 'email':
                $valeur = strtolower($valeur);

                if (preg_match_all("/([^a-z0-9_@.-])/i", $valeur, $trouve)) {
                    $this->error($nom, implode(" ", $trouve[0]));
                    exit();
                }

                if (!preg_match("/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}\$/i", $valeur)) {
                    $this->error($nom, "Format email invalide");
                    exit();
                }
                break;

            case '0-9':
                if (preg_match_all("/([^0-9])/i", $valeur, $trouve)) {
                    $this->error($nom, implode(' ', $trouve[0]));
                    exit();
                }
                break;

            case '0-9extend':
                if (preg_match_all("/([^0-9_\+\-\*\/\)\]\(\[\& ])/i", $valeur, $trouve)) {
                    $this->error($nom, implode(' ', $trouve[0]));
                    exit();
                }
                break;

            case '0-9number':
                if (preg_match_all("/([^0-9+-., ])/i", $valeur, $trouve)) {
                    $this->error($nom, implode(' ', $trouve[0]));
                    exit();
                }
                break;

            case 'date':
                $date = explode('/', $valeur);

                if (count($date) == 3) {
                    settype($date[0], 'integer');
                    settype($date[1], 'integer');
                    settype($date[2], 'integer');

                    if (!checkdate($date[1], $date[0], $date[2])) {
                        $this->error($nom, 'Date non valide');
                        exit();
                    }
                } else {
                    $this->error($nom, 'Date non valide');
                    exit();
                }
                break;

            default:
                break;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $ibid
     * @param [type] $car
     * @return void
     */
    public function error($ibid, $car)
    {
        echo '<div class="alert alert-danger">' . aff_langue($ibid) . ' =&#62; <span>' . stripslashes($car) . '</span></div>';

        if ($this->form_method == '') {
            $this->form_method = "post";
        }

        echo "<form action=\"" . $this->url . "\" method=\"" . $this->form_method . "\" name=\"" . $this->form_title . "\" enctype=\"multipart/form-data\">";
        echo $this->print_form_hidden();
        echo '<input class="btn btn-secondary" type="submit" name="sformret" value="Retour" />
        </form>';

        include("footer.php");
    }

    /**
     * Undocumented function
     *
     * @param [type] $pas
     * @param [type] $mess_passwd
     * @param [type] $mess_ok
     * @param string $presentation
     * @return void
     */
    public function sform_browse_mysql($pas, $mess_passwd, $mess_ok, $presentation = '')
    {
        $result = sql_query("SELECT key_value, passwd FROM sform WHERE id_form='" . $this->form_title . "' AND id_key='" . $this->form_key . "' ORDER BY key_value ASC");
        
        echo "<form action=\"" . $this->url . "\" method=\"post\" name=\"browse\" enctype=\"multipart/form-data\">";
        echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\" class=\"ligna\"><tr><td>";
        echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\" class=\"lignb\">";

        $hidden = false;
        if (substr($mess_ok, 0, 1) == "!") {
            $mess_ok = substr($mess_ok, 1);
            $hidden = true;
        }

        $ibid = 0;

        while (list($key_value, $passwd) = sql_fetch_row($result)) {
            if ($ibid == 0) {
                echo "<tr class=\"ligna\">";
            }

            $ibid++;

            if ($passwd != "") {
                $red = "<span class=\"text-danger\">$key_value (v)</span>";
            } else {
                $red = "$key_value";
            }

            if ($presentation == "liste") {
                echo "<td><a href=\"" . $this->url . "&amp;" . $this->submit_value . "=$mess_ok&amp;browse_key=" . urlencode($key_value) . "\" class=\"noir\">$key_value</a></td>";
            } else {
                echo "<td><input type=\"radio\" name=\"browse_key\" value=\"" . urlencode($key_value) . "\"> $red</td>";
            }

            if ($ibid >= $pas) {
                echo "</tr>";
                $ibid = 0;
            }
        }

        echo "</table><br />";

        if ($this->form_password_access != "") {
            echo "$mess_passwd : <input class=\"textbox_standard\" type=\"password\" name=\"password\" value=\"\"> - ";
        }

        if (!$hidden) {
            echo "<input class=\"bouton_standard\" type=\"submit\" name=\"" . $this->submit_value . "\" value=\"$mess_ok\">";
        }

        echo "</td></tr></table></form>";
    }

    /**
     * Undocumented function
     *
     * @param [type] $clef
     * @return void
     */
    public function sform_read_mysql($clef)
    {
        if ($clef != '') {

            $clef = urldecode($clef);
            $result = sql_query("SELECT content FROM sform WHERE id_form='" . $this->form_title . "' AND id_key='" . $this->form_key . "' AND key_value='" . addslashes($clef) . "' AND passwd='" . $this->form_password_access . "' ORDER BY key_value ASC");
            $tmp = sql_fetch_assoc($result);
            
            if ($tmp) {
                $ibid = explode("\n", $tmp['content']);

                settype($op, 'string');

                foreach ($ibid as $num => $line) {
                    $op = $this->read_load_sform_data(stripslashes($line), $op);
                }

                return true;
            } else
                return false;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $response
     * @return void
     */
    public function sform_insert_mysql($response)
    {
        $content = $this->write_sform_data($response);

        $sql = "INSERT INTO sform (id_form, id_key, key_value, passwd, content) ";
        $sql .= "VALUES ('" . $this->form_title . "', '" . $this->form_key . "', '" . $this->form_key_value . "', '" . $this->form_password_access . "', '$content')";
        
        if (!$result = sql_query($sql)) {
            return "Error Sform : Insert DB";
        }
    }

    /**************************************************************************************/
    public function sform_delete_mysql()
    {
        $sql = "DELETE FROM sform WHERE id_form='" . $this->form_title . "' AND id_key='" . $this->form_key . "' AND key_value='" . $this->form_key_value . "'";
        
        if (!$result = sql_query($sql)) {
            return "Error Sform : Delete DB";
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $response
     * @return void
     */
    public function sform_modify_mysql($response)
    {
        $content = $this->write_sform_data($response);

        $sql = "UPDATE sform SET passwd='" . $this->form_password_access . "', content='$content' WHERE (id_form='" . $this->form_title . "' AND id_key='" . $this->form_key . "' AND key_value='" . $this->form_key_value . "')";
        
        if (!$result = sql_query($sql)) {
            return "Error Sform : Update DB";
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $clef
     * @return void
     */
    public function sform_read_mysql_XML($clef)
    {
        if ($clef != "") {
            $clef = urldecode($clef);

            $result = sql_query("SELECT content FROM sform WHERE id_form='" . $this->form_title . "' AND id_key='" . $this->form_key . "' AND key_value='$clef' AND passwd='" . $this->form_password_access . "' ORDER BY key_value ASC");
            $tmp = sql_fetch_assoc($result);

            $analyseur_xml = xml_parser_create();

            xml_parser_set_option($analyseur_xml, XML_OPTION_CASE_FOLDING, 0);
            xml_parse_into_struct($analyseur_xml, $tmp['content'], $value, $tag);

            $this->sform_XML_tag($value);

            xml_parser_free($analyseur_xml);
            
            return true;
        } else {
            return false;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public function sform_XML_tag($value)
    {
        foreach ($value as $num => $val) {
            if ($val['type'] == 'complete') { // open, complete, close

                $nom    = $val['tag'];       // Le nom du tag
                $valeur = $val['value'];     // La valeur du champs
                $idchamp = $this->interro_fields($nom);

                switch ($value[$num - 1]['tag']) {
                    case "TEXT":
                        $valeur = str_replace("&lt;BR /&gt;", chr(13) . chr(10), $valeur);
                        $valeur = str_replace("&lt;br /&gt;", chr(13) . chr(10), $valeur);
                        $this->form_fields[$idchamp]['value'] = $valeur;
                        break;

                    case "SELECT":
                        $tmp = $this->interro_array($this->form_fields[$idchamp]['value'], $valeur);
                        $this->form_fields[$idchamp]['value'][$tmp]['selected'] = true;
                        break;

                    case "RADIO":
                        $tmp = $this->interro_array($this->form_fields[$idchamp]['value'], $valeur);
                        $this->form_fields[$idchamp]['value'][$tmp]['checked'] = true;
                        break;

                    case "CHECK":
                        if ($valeur) {
                            $valeur = true;
                        } else {
                            $valeur = false;
                        }

                        $this->form_fields[$idchamp]['checked'] = $valeur;
                        break;

                    case "DATUM":
                        $this->form_fields[$idchamp]['value'] = $valeur;
                        break;
                        
                    case "TIMESTAMP":
                        $this->form_fields[$idchamp]['value'] = $valeur;
                        break;
                }
            }
        }
    }

}
