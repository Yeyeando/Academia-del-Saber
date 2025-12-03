<?php

namespace App\Policies;

use App\Models\Curso;
use App\Models\User;

class CursoPolicy
{
    public function viewAny(?User $user): bool
    {
        // Cualquiera puede ver el listado (incluso sin login)
        return true;
    }
    
    public function view(?User $user, Curso $curso): bool
    {
        // Cualquiera puede ver un curso individual
        return true;
    }
    
    public function create(User $user): bool
    {
        // Solo los administradores pueden crear
        return $user->isAdmin();
    }
    
    public function update(User $user, Curso $curso): bool
    {
        // Solo los administradores pueden editar
        return $user->isAdmin();
    }
    
    public function delete(User $user, Curso $curso): bool
    {
        // Solo los administradores pueden eliminar
        return $user->isAdmin();
    }
}
