<?php

namespace App\Admin\Controllers;

use App\Models\EWCountry;


use App\Models\Platform\LinkageOption;
use App\Models\Platform\ProductForm;
use Platform\Admin\Repositories\PlatformCommon;
use Encore\Admin\Controllers\AdminController;
use Platform\Admin\Auth\Database\Administrator;
use Platform\Admin\Facades\Admin;
use Platform\Admin\Helpers\Common;
use Platform\Admin\Form;
use Platform\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;


class EWCountryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = "EWCountry";

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $actionPermission = isset(request()->get('routespecificpermission')['action_permission']) ? request()->get('routespecificpermission')['action_permission'] : [];
        $enableFiledList = isset(request()->get('routespecificpermission')['form_field_list']) ? request()->get('routespecificpermission')['form_field_list'] : [];

        $form = new Form(new EWCountry);

        $ew_country_id = (request()->route()->parameter("ew_country")) ? request()->route()->parameter("ew_country") : "";
        if ($ew_country_id) {
            $ew_countryinfo = EWCountry::find($ew_country_id);
        }
        //$form->display("id", "ID");
        $name = $form->text("name", __("Name"))->required();
        $form->text("iso_code", __("ISO Code"))->required();
        $form->text("calling_code", __("Calling Code"))->required();
        $status = $form->select("status", __("Status"))->options(['Active' => 'Active', 'Inactive' => 'Inactive']);
        $form->image("flag", __("Country Flag"))->uniqueName();


        //user reference fields create here
        $authenticableUser = Admin::user();
        Common::add_hidden_ref_form_fields($form, $authenticableUser, true);
        // callback after form submission
        $form->submitted(function (Form $form) {

        });

        // callback before save
        $form->saving(function (Form $form) {

        });

        // callback after save
        $form->saved(function (Form $form) {

        });
        $productFormSettings = ProductForm::where("model_name", "EWCountry")->first();
        if ($productFormSettings->platform_form_class) {
            $form->addFormClassScript($productFormSettings->platform_form_class);
        }
        return $form;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $enableFiledList = isset(request()->get('routespecificpermission')['field_list']) ? request()->get('routespecificpermission')['field_list'] : [];
        $listRefidCondition = isset(request()->get('routespecificpermission')['list_refid_condition']) ? request()->get('routespecificpermission')['list_refid_condition'] : [];
        $listCustomCondition = isset(request()->get('routespecificpermission')['list_custom_condition']) ? request()->get('routespecificpermission')['list_custom_condition'] : [];
        $createIdCondition = isset(request()->get('routespecificpermission')['create_id_condition']) ? request()->get('routespecificpermission')['create_id_condition'] : [];
        $createRefidCondition = isset(request()->get('routespecificpermission')['create_refid_condition']) ? request()->get('routespecificpermission')['create_refid_condition'] : [];
        $editIdCondition = isset(request()->get('routespecificpermission')['edit_id_condition']) ? request()->get('routespecificpermission')['edit_id_condition'] : [];
        $editRefidCondition = isset(request()->get('routespecificpermission')['edit_refid_condition']) ? request()->get('routespecificpermission')['edit_refid_condition'] : [];
        $editCustomCondition = isset(request()->get('routespecificpermission')['edit_custom_condition']) ? request()->get('routespecificpermission')['edit_custom_condition'] : [];
        $deleteIdCondition = isset(request()->get('routespecificpermission')['delete_id_condition']) ? request()->get('routespecificpermission')['delete_id_condition'] : [];
        $deleteRefidCondition = isset(request()->get('routespecificpermission')['delete_refid_condition']) ? request()->get('routespecificpermission')['delete_refid_condition'] : [];
        $deleteCustomCondition = isset(request()->get('routespecificpermission')['delete_custom_condition']) ? request()->get('routespecificpermission')['delete_custom_condition'] : [];
        $actionPermission = isset(request()->get('routespecificpermission')['action_permission']) ? request()->get('routespecificpermission')['action_permission'] : [];
        $operatorBetweenBlockConditionRules = isset(request()->get('routespecificpermission')['operator_between_block_condition_rules']) ? request()->get('routespecificpermission')['operator_between_block_condition_rules'] : [];

        $grid = new Grid(new EWCountry);

        $grid->column("id", __("Id"));
        if (empty($enableFiledList)) {
            $grid->column("name", __("Name"))->display(function ($name) {
                return empty($name) ? "" : $name;
            });
            $grid->column("status", __("Status"));
            $grid->column("iso_code", __("Iso Code"));
            $grid->column("calling_code", __("Calling Code"));
            $grid->column("flag", __("Country Flag"))->image();
        } else {
            foreach ($enableFiledList as $column) {
                switch ($column[2]) {
                    case 'image':
                        $grid->column($column[0], __($column[1]))->image();
                        break;
                    case 'file':
                        $grid->column($column[0], __($column[1]))->file();
                        break;
                    default:
                        $grid->column($column[0], __($column[1]));
                        break;
                }
            }
        }

        $productFormSettings = ProductForm::where("model_name", "EWCountry")->first();
        $enableAdministratorPermissionByDefault = $productFormSettings->enable_administrator_permission;

        //except super user
        $gridquery = $grid->model();
        if ((Admin::user()->organization_id != 1) || ((Admin::user()->organization_id == 1) && ($enableAdministratorPermissionByDefault == '0'))) {
            $gridquery->where(function ($query) use ($listRefidCondition, $listCustomCondition) {
                if ($listRefidCondition) {
                    $query->whereHas('creator', function ($query) use ($listRefidCondition) {
                        foreach ($listRefidCondition as $key => $condition) {
                            if (isset($condition['condition_field']) && isset($condition['condition_op']) && $condition['condition_field'] != '' && $condition['condition_op'] != '') {
                                switch ($condition['joining_rules']) {
                                    case 'NA':
                                        $query->where($condition['condition_field'], $condition['condition_op'], ($condition['condition_op'] == 'like') ? "%" . Admin::user()->{$condition['condition_field']} . "%" : Admin::user()->{$condition['condition_field']});
                                        break;
                                    case 'AND':
                                        $query->where($condition['condition_field'], $condition['condition_op'], ($condition['condition_op'] == 'like') ? "%" . Admin::user()->{$condition['condition_field']} . "%" : Admin::user()->{$condition['condition_field']});
                                        break;
                                    case 'OR':
                                        $query->orWhere($condition['condition_field'], $condition['condition_op'], ($condition['condition_op'] == 'like') ? "%" . Admin::user()->{$condition['condition_field']} . "%" : Admin::user()->{$condition['condition_field']});
                                        break;
                                }
                            }
                        }
                    });
                }
                foreach ($listCustomCondition as $key => $condition) {
                    $condition_value = (isset($condition['condition_value_auth']) && ($condition['condition_value_auth'])) ? Admin::user()->{$condition['condition_value_auth']} : $condition['condition_value'];
                    if (isset($condition['list_custom_condition_field']) && isset($condition['condition_op']) && ($condition_value) && $condition['list_custom_condition_field'] != '' && $condition['condition_op'] != '') {
                        switch ($condition['joining_rules']) {
                            case 'NA':
                                $query->where($condition['list_custom_condition_field'], $condition['condition_op'], ($condition['condition_op'] == 'like') ? "%" . $condition_value . "%" : $condition_value);
                                break;
                            case 'AND':
                                $query->where($condition['list_custom_condition_field'], $condition['condition_op'], ($condition['condition_op'] == 'like') ? "%" . $condition_value . "%" : $condition_value);
                                break;
                            case 'OR':
                                $query->orWhere($condition['list_custom_condition_field'], $condition['condition_op'], ($condition['condition_op'] == 'like') ? "%" . $condition_value . "%" : $condition_value);
                                break;
                        }
                    }
                }

            });
        }

        $disableCreateButton = true;
        $checkCreateButtonPermission = PlatformCommon::checkCreateButtonPermission($actionPermission, $createIdCondition);
        if (($checkCreateButtonPermission) || ((Admin::user()->organization_id == 1) && ($enableAdministratorPermissionByDefault == '1'))) {
            $disableCreateButton = false;
        }
        $grid->disableCreateButton($disableCreateButton);

        $gridSetting = ($productFormSettings->grid_action_btn) ? $productFormSettings->grid_action_btn : [];
        $grid->actions(function ($actions) use ($enableAdministratorPermissionByDefault, $actionPermission, $gridSetting, $editIdCondition, $editRefidCondition, $editCustomCondition, $deleteIdCondition, $deleteRefidCondition, $deleteCustomCondition, $operatorBetweenBlockConditionRules) {
            $disableViewButton = true;
            $disableEditButton = true;
            $disableDeleteButton = true;
            if (
                in_array("view", $gridSetting) && (in_array("View", $actionPermission) || ((Admin::user()->organization_id == 1) && ($enableAdministratorPermissionByDefault == '1')))
            ) {
                $disableViewButton = false;
            }

            $checkEditButtonPermission = PlatformCommon::checkEditButtonPermission($actionPermission, $editIdCondition, $editRefidCondition, $editCustomCondition, $actions->row, $operatorBetweenBlockConditionRules);
            $checkDeleteButtonPermission = PlatformCommon::checkDeleteButtonPermission($actionPermission, $deleteIdCondition, $deleteRefidCondition, $deleteCustomCondition, $actions->row, $operatorBetweenBlockConditionRules);

            if (in_array("edit", $gridSetting) && ($checkEditButtonPermission || ((Admin::user()->organization_id == 1) && ($enableAdministratorPermissionByDefault == '1')))) {
                $disableEditButton = false;
            }
            if (in_array("delete", $gridSetting) && ($checkDeleteButtonPermission || ((Admin::user()->organization_id == 1) && ($enableAdministratorPermissionByDefault == '1')))) {
                $disableDeleteButton = false;
            }

            $actions->disableView($disableViewButton);
            $actions->disableEdit($disableEditButton);
            $actions->disableDelete($disableDeleteButton);
            if (in_array("buy_now_btn", $gridSetting)) {
                $buyNowURL = "#";
                $actions->append('&nbsp;<a href="' . $buyNowURL . '" class="grid-row-edit" title="Buy Now"><i class="fa fa-shopping-cart"></i></a>');
            }
        });

        if (!in_array("enable_filter", $gridSetting)) {
            $grid->disableFilter();
        }
        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            $filter->like("name", __("Name"));
            $filter->equal("status", __("Status"))->select(['Active' => 'Active', 'Inactive' => 'Inactive']);

        });

        if ($productFormSettings->platform_grid_class) {
            $grid->addGridClassScript($productFormSettings->platform_grid_class);
        }
        if (in_array("enable_row_selector", $gridSetting)) {
            $grid->disableRowSelector(false);
        }
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(EWCountry::findOrFail($id));

        //$show->id("ID");
        $show->field("name", __("Name"));
        $show->field("status", __("Status"));
        $show->field("iso_code", __("Iso Code"));
        $show->field("calling_code", __("Calling Code"));
        $show->field("flag", __("Country Flag"))->image();

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                //$tools->disableList();
                $tools->disableDelete();
            });

        return $show;
    }

    public function getUseTableName()
    {
        return 'ew_countries';
    }


}
