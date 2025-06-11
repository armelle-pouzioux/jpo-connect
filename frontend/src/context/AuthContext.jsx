// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect } from 'react';
import { getCurrentUser, getStoredUser, logout as apiLogout } from '../api/auth';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Vérifier si l'utilisateur est connecté au chargement
    const storedUser = getStoredUser();
    if (storedUser) {
      setUser(storedUser);
      // Vérifier avec le serveur
      getCurrentUser()
        .then(result => {
          if (result.success) {
            setUser(result.user);
          } else {
            setUser(null);
            localStorage.removeItem('token');
            localStorage.removeItem('user');
          }
        })
        .catch(() => {
          setUser(null);
          localStorage.removeItem('token');
          localStorage.removeItem('user');
        })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const login = (userData) => {
    setUser(userData);
  };

  const logout = async () => {
    try {
      await apiLogout();
    } catch (error) {
      console.error('Erreur lors de la déconnexion:', error);
    }
    setUser(null);
  };

  const value = {
    user,
    login,
    logout,
    loading,
    isLoggedIn: !!user,
    hasRole: (role) => {
      if (!user) return false;
      if (Array.isArray(role)) {
        return role.includes(user.role);
      }
      return user.role === role;
    }
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}