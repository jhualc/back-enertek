<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileUserGeneralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->resource->id,
            "name"=>$this->resource->name,
            "email" =>$this->resource->email,
            "email_verified_at" => $this->resource->emaiul_verified_at,
            "password" =>$this->resource->password,
            "remember_token" =>$this->resource->remember_token,
            "created_at" =>$this->resource->created_at,
            "updated_at" =>$this->resource->updated_at,
            "usr_empresa" =>$this->resource->usr_empresa,
            "usr_cargo" =>$this->resource->usr_cargo,
            "usr_perfil" =>$this->resource->usr_perfil,
            "avatar"=> $this->resource->usr_avatar ? "".$this->resource->usr_avatar : "non-avatar.png",
            "usr_datos_personales" => $this->resource->usr_datos_personales

        ];
    }
}
