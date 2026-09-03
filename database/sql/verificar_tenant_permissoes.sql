-- Exibe o tenant, usuário, perfil e todas as permissões associadas.
-- O filtro pelo tenant em cada JOIN também ajuda a identificar vínculos
-- incorretos entre registros pertencentes a tenants diferentes.
SELECT
    t.id AS tenant_id,
    t.name AS tenant,
    t.slug AS tenant_slug,
    u.id AS user_id,
    u.name AS usuario,
    u.email,
    r.id AS role_id,
    r.name AS perfil,
    r.parent_id AS role_parent_id,
    r._lft AS role_lft,
    r._rgt AS role_rgt,
    p.id AS permission_id,
    p.name AS permissao,
    p.slug AS permission_slug
FROM tenants AS t
INNER JOIN users AS u
    ON u.tenant_id = t.id
INNER JOIN roles AS r
    ON r.id = u.role_id
   AND r.tenant_id = t.id
LEFT JOIN role_permission AS rp
    ON rp.role_id = r.id
   AND rp.tenant_id = t.id
LEFT JOIN permissions AS p
    ON p.id = rp.permission_id
   AND p.tenant_id = t.id
WHERE t.slug = 'tenant-padrao'
ORDER BY u.name, r._lft, p.name;
