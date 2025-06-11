// src/components/Home.jsx
import { Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

export default function Home() {
  const { isLoggedIn, user } = useAuth();

  return (
    <div className="max-w-4xl mx-auto">
      <div className="text-center py-16">
        <h1 className="text-4xl font-bold text-gray-800 mb-6">
          Bienvenue sur JPO Connect
        </h1>
        <p className="text-xl text-gray-600 mb-8">
          Plateforme de gestion des Journées Portes Ouvertes
        </p>

        {isLoggedIn ? (
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-2xl font-semibold mb-4">
              Bonjour {user.name} {user.surname} !
            </h2>
            <p className="text-gray-600 mb-6">
              Vous êtes connecté en tant que <strong>{user.role}</strong>
            </p>
            <div className="space-x-4">
              <Link 
                to="/dashboard" 
                className="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-block"
              >
                Accéder au Dashboard
              </Link>
              {(user.role === 'manager' || user.role === 'director') && (
                <Link 
                  to="/admin" 
                  className="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg inline-block"
                >
                  Administration
                </Link>
              )}
            </div>
          </div>
        ) : (
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-2xl font-semibold mb-4">
              Commencez dès maintenant
            </h2>
            <p className="text-gray-600 mb-6">
              Connectez-vous pour accéder aux fonctionnalités de la plateforme
            </p>
            <div className="space-x-4">
              <Link 
                to="/login" 
                className="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg inline-block"
              >
                Se connecter
              </Link>
              <Link 
                to="/register" 
                className="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg inline-block"
              >
                S'inscrire
              </Link>
            </div>
          </div>
        )}
      </div>

      {/* Section informative */}
      <div className="grid md:grid-cols-3 gap-8 mt-16">
        <div className="bg-white p-6 rounded-lg shadow-md text-center">
          <h3 className="text-xl font-semibold mb-3">📅 Gestion des JPO</h3>
          <p className="text-gray-600">
            Créez et gérez facilement vos journées portes ouvertes
          </p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow-md text-center">
          <h3 className="text-xl font-semibold mb-3">👥 Inscriptions</h3>
          <p className="text-gray-600">
            Suivez les inscriptions et la participation en temps réel
          </p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow-md text-center">
          <h3 className="text-xl font-semibold mb-3">💬 Commentaires</h3>
          <p className="text-gray-600">
            Recueillez et modérez les retours des participants
          </p>
        </div>
      </div>
    </div>
  );
}