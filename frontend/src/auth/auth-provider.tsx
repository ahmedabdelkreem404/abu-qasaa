"use client";

import { usePathname, useRouter } from "next/navigation";
import {
  clearStoredToken,
  getCurrentUser,
  getStoredToken,
  login as loginRequest,
  logout as logoutRequest,
} from "@/api/client";
import type { AuthUser } from "@/types/platform";
import {
  createContext,
  type ReactNode,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";

type AuthContextValue = {
  user: AuthUser | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
  hasPermission: (permission: string) => boolean;
};

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const refreshUser = useCallback(async () => {
    const token = getStoredToken();

    if (!token) {
      setUser(null);
      setIsLoading(false);
      return;
    }

    try {
      const response = await getCurrentUser();
      setUser(response.data);
    } catch {
      clearStoredToken();
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    const timeout = window.setTimeout(() => {
      void refreshUser();
    }, 0);

    return () => window.clearTimeout(timeout);
  }, [refreshUser]);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isLoading,
      login: async (email, password) => {
        const response = await loginRequest(email, password);
        setUser(response.data.user);
      },
      logout: async () => {
        await logoutRequest();
        setUser(null);
      },
      refreshUser,
      hasPermission: (permission) =>
        Boolean(user?.roles.includes("super_admin") || user?.permissions.includes(permission)),
    }),
    [isLoading, refreshUser, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error("useAuth must be used inside AuthProvider");
  }

  return context;
}

export function ProtectedDashboard({ children }: { children: ReactNode }) {
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (!isLoading && !user) {
      router.replace(`/login?next=${encodeURIComponent(pathname)}`);
    }
  }, [isLoading, pathname, router, user]);

  if (isLoading) {
    return <div className="p-6 text-sm text-slate-600">Loading dashboard...</div>;
  }

  if (!user) {
    return <div className="p-6 text-sm text-slate-600">Redirecting to login...</div>;
  }

  return children;
}
