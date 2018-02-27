export const USER = 'user';
export const MANAGER = 'manager';
export const ADMIN = 'admin';

export const selectOptions = [
  { value: USER, text: 'User' },
  { value: MANAGER, text: 'Manager' },
  { value: ADMIN, text: 'Admin' },
];

export function isManager(role) {
  return [MANAGER, ADMIN].includes(role);
}
