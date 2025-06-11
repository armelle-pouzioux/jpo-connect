// src/App.jsx
import { BrowserRouter as Router, Routes, Route, Link, Navigate } from "react-router-dom";
import { AuthProvider, useAuth } from "./context/AuthContext";
import Dashboard from "./components/Dashboard";
import LoginForm from "./components/LoginForm";
import RegisterForm from "./components/RegisterForm";
import Home from "./components/Home"; // À créer

// Composant pour les routes protégées
function ProtectedRoute({ children, requiredRoles = [] }) {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="flex justify-center items-center h-64">Chargement...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (requiredRoles.length > 0 && !requiredRoles.includes(user.role)) {
    return (
      <div className="text-center p-8">
        <h2 className="text-xl text-red-600">Accès refusé</h2>
        <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
      </div>
    );
  }

  return children;
}

// Composant de navigation
function Navigation() {
  const { user, logout, isLoggedIn } = useAuth();

  return (
    <nav className="bg-blue-500 text-white p-4 shadow-md">
      <div className="container mx-auto flex justify-between items-center">
        <div className="flex space-x-4">
          <Link to="/" className="hover:text-blue-200 font-semibold">
            JPO Connect
          </Link>
          
          {isLoggedIn && (
            <>
              <Link to="/dashboard" className="hover:text-blue-200">
                Dashboard
              </Link>
              {user?.role === 'admin' && (
                <Link to="/admin" className="hover:text-blue-200">
                  Administration
                </Link>
              )}
            </>
          )}
        </div>

        <div className="flex items-center space-x-4">
          {isLoggedIn ? (
            <>
              <span className="text-sm">
                Bonjour {user.name} {user.surname} 
                <span className="text-blue-200">({user.role})</span>
              </span>
              <button 
                onClick={logout}
                className="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm"
              >
                Déconnexion
              </button>
            </>
          ) : (
            <>
              <Link 
                to="/login" 
                className="bg-green-500 hover:bg-green-600 px-3 py-1 rounded text-sm"
              >
                Connexion
              </Link>
              <Link 
                to="/register" 
                className="bg-purple-500 hover:bg-purple-600 px-3 py-1 rounded text-sm"
              >
                Inscription
              </Link>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}

// Composant principal de l'application
function AppContent() {
  const { loading } = useAuth();

  if (loading) {
    return (
      <div className="flex justify-center items-center h-screen">
        <div className="text-xl">Chargement de l'application...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <Navigation />
      
      <main className="container mx-auto py-8">
        <Routes>
          {/* Route publique */}
          <Route path="/" element={<Home />} />
          
          {/* Routes d'authentification */}
          <Route 
            path="/login" 
            element={<LoginForm onSuccess={() => window.location.href = '/dashboard'} />} 
          />
          <Route 
            path="/register" 
            element={<RegisterForm onSuccess={() => window.location.href = '/login'} />} 
          />
          
          {/* Routes protégées */}
          <Route 
            path="/dashboard" 
            element={
              <ProtectedRoute requiredRoles={['user', 'employee', 'manager', 'director']}>
                <Dashboard />
              </ProtectedRoute>
            } 
          />
          
          {/* Route admin (exemple) */}
          <Route 
            path="/admin" 
            element={
              <ProtectedRoute requiredRoles={['manager', 'director']}>
                <div className="text-center">
                  <h1 className="text-2xl font-bold">Administration</h1>
                  <p>Interface d'administration (à développer)</p>
                </div>
              </ProtectedRoute>
            } 
          />
          
          {/* Route 404 */}
          <Route 
            path="*" 
            element={
              <div className="text-center py-16">
                <h1 className="text-4xl font-bold text-gray-600">404</h1>
                <p className="text-gray-500 mt-4">Page non trouvée</p>
                <Link to="/" className="text-blue-500 hover:underline mt-4 inline-block">
                  Retour à l'accueil
                </Link>
              </div>
            } 
          />
        </Routes>
      </main>
    </div>
  );
}

// Composant racine avec le provider d'authentification
function App() {
  return (
    <AuthProvider>
      <Router>
        <AppContent />
      </Router>
    </AuthProvider>
  );
}

export default App;