import { apiGet, apiPost } from "./api";
import type { AuthResult, User } from "../types/auth";

interface RawUser {
  uuid: string;
  name: string;
  email: string;
  created_at: string;
}

interface RawAuthResult {
  user: RawUser;
  token: string;
}

function mapUser(raw: RawUser): User {
  return {
    uuid: raw.uuid,
    name: raw.name,
    email: raw.email,
    createdAt: raw.created_at,
  };
}

function mapAuthResult(raw: RawAuthResult): AuthResult {
  return {
    user: mapUser(raw.user),
    token: raw.token,
  };
}

export async function register(
  name: string,
  email: string,
  password: string,
  passwordConfirmation: string,
): Promise<AuthResult> {
  const response = await apiPost<{ data: RawAuthResult }>("/auth/register", {
    name,
    email,
    password,
    password_confirmation: passwordConfirmation,
  });

  return mapAuthResult(response.data);
}

export async function login(email: string, password: string): Promise<AuthResult> {
  const response = await apiPost<{ data: RawAuthResult }>("/auth/login", { email, password });

  return mapAuthResult(response.data);
}

export async function logout(): Promise<void> {
  await apiPost<void>("/auth/logout", {});
}

export async function getMe(): Promise<User> {
  const response = await apiGet<{ data: RawUser }>("/auth/me");

  return mapUser(response.data);
}
