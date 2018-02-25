export const USER = 'user';
export const MANAGER = 'manager';
export const ADMIN = 'admin';

export function isManager(role) {
  return [MANAGER, ADMIN].includes(role);
}
