<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = ['name', 'slug', 'groupby'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    public function rolespermssion()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    public static function getPermission()
    {
        $permissions = Permission::orderBy('id')->get();

        $result = [];

        $groupedPermissions = $permissions->groupBy('groupby');

        foreach ($groupedPermissions as $groupKey => $groupItems) {
            $data = [];
            $firstItem = $groupItems->first(); 

            $data['id'] = $firstItem->id;
            $data['name'] = $firstItem->name;
            $data['slug'] = $firstItem->slug;

            $group = [];

            foreach ($groupItems as $per) {
                $group[] = [
                    'id' => $per->id,
                    'name' => $per->name,
                    'slug' => $per->slug,
                ];
            }
            $data['group'] = $group;
            $result[] = $data;
        }

        return $result;
    }


    public static function hasPermission($slug,$role_id){
        $permission = Permission::where('slug', $slug)->first();
        if($permission){
            $role = Role::find($role_id);
            return $role->permissions->contains($permission);
        }
        return false;
    }
}
