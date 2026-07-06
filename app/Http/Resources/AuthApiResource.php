<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->emp_id,
            'business_id' => $this->emp_b_id,
            'branch_id' =>$this->emp_br_id,
            'email' => $this->emp_email,
            'emp_code' => $this->emp_code ?? '',
            'emp_id' => $this->emp_id ?? '',
            'country_code' => '',
            'phone' => $this->emp_phone,
            'notification_key' => $this->emp_fcm_token ?? '',
            'profile_photo' => $this->emp_profile_photo ? url('/uploads/employee_profile/'.$this->emp_profile_photo) :  '',
            'role' => RoleApiResource::collection([$this->fh_role])->first(),
            'name' => $this->emp_full_name,
            'is_verified' => $this->fh_business->b_is_verified,
            'is_employee' => $this->emp_role_id == 1 ? false : true,
            'is_password' => isset($this->emp_password) && ($this->emp_password !== null)? true : false,
            'is_approval_manager' => (boolean)$this->is_approval_manager,
        ];
    }
}
