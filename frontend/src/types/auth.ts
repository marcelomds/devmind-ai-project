export interface User {
  uuid: string;
  name: string;
  email: string;
  createdAt: string;
}

export interface AuthResult {
  user: User;
  token: string;
}
