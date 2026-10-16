<?php
declare(strict_types=1);
final class AccountSettings {
    public static function save(PDO $db,array $actor,array $input): array {
        $q=$db->prepare('SELECT * FROM admin_users WHERE id=?');$q->execute([$actor['id']]);$user=$q->fetch(PDO::FETCH_ASSOC);
        if(!password_verify((string)($input['current_password']??''),$user['password']))throw new InvalidArgumentException('Your current password is incorrect.');
        $email=trim((string)($input['email']??''));$password=(string)($input['new_password']??'');
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>60)throw new InvalidArgumentException('Use a valid login email of at most 60 characters.');
        if($password!==''&&(strlen($password)<12||strlen($password)>72))throw new InvalidArgumentException('Use a password of 12–72 characters.');
        if(!preg_match('/^[a-zA-Z0-9_]+$/',PLATFORM_DB))throw new RuntimeException('Invalid platform database.');
        $db->beginTransaction();
        try{
            $db->prepare('UPDATE admin_users SET email=?,username=?,password=? WHERE id=?')->execute([$email,$email,$password===''?$user['password']:password_hash($password,PASSWORD_DEFAULT),$actor['id']]);
            $db->prepare('UPDATE `'.PLATFORM_DB.'`.sites SET owner_email=? WHERE id=?')->execute([$email,$actor['site_id']]);
            $db->commit();$_SESSION['admin_user']['email']=$email;$_SESSION['admin_user']['username']=$email;
            return ['saved'=>true];
        }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
    }
    public static function slug(PDO $db,array $actor,array $input): array {
        $slug=strtolower(trim((string)($input['slug']??'')));
        if(!TenantContext::validSlug($slug))throw new InvalidArgumentException('Choose a unique address with 3–40 lowercase letters, numbers or hyphens. Platform names are reserved.');
        $registry=TenantContext::registry();
        try{$registry->prepare('UPDATE sites SET slug=? WHERE id=?')->execute([$slug,$actor['site_id']]);}
        catch(PDOException $e){if($e->getCode()==='23000')throw new InvalidArgumentException('That website address is already taken.');throw $e;}
        return ['url'=>rtrim(PLATFORM_URL,'/').'/s/'.$slug];
    }
}
