<?php

namespace App\Admin\Controllers;

use App\Models\EWCity;
use App\Models\EWCountry;
use App\Models\EWUserDetailChild as EWUserDetail;

use App\Models\LinkageOption;
use App\Models\ProductForm;
use Platform\Admin\Helpers\Common;
use Platform\Admin\Repositories\PlatformCommon;
use App\Admin\Controllers\EWUserDetailController;
use Platform\Admin\Auth\Database\Administrator;
use Platform\Admin\Facades\Admin;
use Platform\Admin\Form;
use Platform\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class EWUserDetailChildController extends EWUserDetailController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = "User Detail";

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $actionPermission = isset(request()->get('routespecificpermission')['action_permission']) ? request()->get('routespecificpermission')['action_permission'] : [];
        $enableFiledList = isset(request()->get('routespecificpermission')['form_field_list']) ? request()->get('routespecificpermission')['form_field_list'] : [];

        $form = new Form(new EWUserDetail);

        $ew_user_detail_id = (request()->route()->parameter("ew_user_detail")) ? request()->route()->parameter("ew_user_detail") : "";
        if ($ew_user_detail_id) {
            $ew_user_detailinfo = EWUserDetail::find($ew_user_detail_id);
        }
        //$form->display("id", "ID");
        //$user_id = $form->number("user_id", __("User ID"))->required();
        $gender = $form->select("gender", __("Gender"))->options(['male' => 'male', 'female' => 'female', 'other' => 'other'])->required();
        $date_of_birth = $form->date("date_of_birth", __("Date of Birth"));
        $permanent_address = $form->text("permanent_address", __("Permanent Address"));
        $nid_number = $form->text("nid_number", __("NID Number"));
        $form->image("id_card_image", __("ID Card Image"))->uniqueName();
        $form->image("selfie_image", __("Selfie Image"))->uniqueName();
        $status = $form->select("status", __("Status"))->options(['approved' => 'approved', ' pending' => ' pending', ' hold' => ' hold', ' rejected' => ' rejected'])->required();
        $comment = $form->text("comment", __("Comment"));
        if (Admin::user()->organization_id != 1) {
            $dropdownqry = EWCountry::select("name", "id");
            if (Admin::user()->account_type == "system") {
                $dropdownqry->where("organization_ref_id", "like", Admin::user()->organization_ref_id . "%");
            } elseif (Admin::user()->account_type == "business") {
                $dropdownqry->where("user_ref_id", "like", Admin::user()->user_ref_id . "%");
            }
            $dropdownqry->where(function ($query) {
                $query->where("status", "=", "Active");
            });
            $optionsEWCountry = $dropdownqry->pluck("name", "id");
        } else {
            $optionsEWCountry = EWCountry::pluck("name", "id");
        }
        $country_id = $form->select("country_id", __("Country"))->options($optionsEWCountry)->required();
        if (Admin::user()->organization_id != 1) {
            $dropdownqry = EWCity::select("name", "id");
            if (Admin::user()->account_type == "system") {
                $dropdownqry->where("organization_ref_id", "like", Admin::user()->organization_ref_id . "%");
            } elseif (Admin::user()->account_type == "business") {
                $dropdownqry->where("user_ref_id", "like", Admin::user()->user_ref_id . "%");
            }
            $dropdownqry->where(function ($query) {
                $query->where("status", "=", "Active");
            });
            $optionsEWCity = $dropdownqry->pluck("name", "id");
        } else {
            $optionsEWCity = EWCity::pluck("name", "id");
        }
        $city_id = $form->select("city_id", __("City"))->options($optionsEWCity)->required();

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
        $productFormSettings = \App\Models\Platform\ProductForm::where("model_name", "EWUserDetail")->first();
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

        $grid = new Grid(new EWUserDetail);

        $grid->column("id", __("Id"));
        if (empty($enableFiledList)) {
            $grid->column("userinfo.name", __("Name"))->display(function ($user_id) {
                return empty($user_id) ? "" : $user_id;
            });
            $grid->column("gender", __("Gender"));
            $grid->column("date_of_birth", __("Date of Birth"))->display(function ($date_of_birth) {
                return empty($date_of_birth) ? "" : $date_of_birth;
            });
            $grid->column("nid_number", __("NID Number"))->display(function ($nid_number) {
                return empty($nid_number) ? "" : $nid_number;
            });
            $grid->column("status", __("Status"));
            $grid->column("comment", __("Comment"))->display(function ($comment) {
                return empty($comment) ? "" : $comment;
            });
            $grid->column("ewcountry_country_id.name", __("Country"))->display(function ($country_id) {
                return empty($country_id) ? "" : $country_id;
            });
            $grid->column("ewcity_city_id.name", __("City"))->display(function ($city_id) {
                return empty($city_id) ? "" : $city_id;
            });
            $grid->column("step", __("Step"))->display(function ($step) {
                return empty($step) ? "" : $step;
            });
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

        $productFormSettings = ProductForm::where("model_name", "EWUserDetail")->first();
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

            // $actions->disableView($disableViewButton);
            // $actions->disableEdit($disableEditButton);
            // $actions->disableDelete($disableDeleteButton);
            $actions->disableView();
            $actions->disableDelete();
            if (in_array("buy_now_btn", $gridSetting)) {
                $buyNowURL = "#";
                $actions->append('&nbsp;<a href="' . $buyNowURL . '" class="grid-row-edit" title="Buy Now"><i class="fa fa-shopping-cart"></i></a>');
            }
        });

        if (!in_array("enable_filter", $gridSetting)) {
            $grid->disableFilter();
        }
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            $filter->like("user_id", __("User ID"));
            $filter->equal("gender", __("Gender"))->select(['male' => 'male', 'female' => 'female', 'other' => 'other']);
            $filter->equal("status", __("Status"))->select(['approved' => 'approved', 'pending' => 'pending', 'hold' => 'hold', 'rejected' => 'rejected']);

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
        $show = new Show(EWUserDetail::findOrFail($id));

        //$show->id("ID");
        $show->field("user_id", __("User ID"));
        $show->field("gender", __("Gender"));
        $show->field("date_of_birth", __("Date of Birth"));
        $show->field("permanent_address", __("Permanent Address"));
        $show->field("nid_number", __("NID Number"));
        $show->field("id_card_image", __("ID Card Image"))->image();
        $show->field("selfie_image", __("Selfie Image"))->image();
        $show->field("status", __("Status"));
        $show->field("comment", __("Comment"));

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
        return 'ew_user_details';
    }

}
