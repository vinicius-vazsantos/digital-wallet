import { useState } from 'react';
// import { useAuth } from '../hooks/useAuth';
import { useNavigate } from 'react-router-dom';
import { 
  Mail, 
  Lock, 
  Eye, 
  EyeOff, 
  LogIn, 
  UserPlus, 
  Key,
  ArrowRight,
  CheckSquare,
  Square
} from 'lucide-react';

const Login = () => {
  const [credentials, setCredentials] = useState({
    email: '',
    password: '',
  });
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  // const { login, loading, error } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setCredentials({
      ...credentials,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      // await login(credentials);
      navigate('/accounts');
    } catch (err) {
      console.error('Login error:', err);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="w-full max-w-lg mx-auto">
        {/* Card principal */}
        <div className="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/70 overflow-hidden">
          <div className="p-8">
            {/* Cabeçalho */}
            <div className="text-center mb-8">
              <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-md mb-4">
                <Key className="h-8 w-8 text-white" />
              </div>
              <h2 className="text-2xl font-bold text-slate-800 mb-2">Bem-vindo de volta</h2>
              <p className="text-slate-500">Entre para acessar sua conta</p>
            </div>

            {/* Formulário */}
            <form className="space-y-5 text-start" onSubmit={handleSubmit}>
              {/* {error && (
                <div className="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-center">
                  <div className="flex-1">
                    <p className="text-sm">{error}</p>
                  </div>
                </div>
              )} */}

              {/* Campo Email */}
              <div>
                <label htmlFor="email" className="block text-sm font-medium text-slate-700 mb-1">
                  Email
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Mail className="h-5 w-5 text-slate-400" />
                  </div>
                  <input
                    id="email"
                    name="email"
                    type="email"
                    autoComplete="email"
                    required
                    className="block w-full pl-10 pr-3 py-3 text-slate-600 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    placeholder="seu@email.com"
                    value={credentials.email}
                    onChange={handleChange}
                  />
                </div>
              </div>

              {/* Campo Senha */}
              <div>
                <label htmlFor="password" className="block text-sm font-medium text-slate-700 mb-1">
                  Senha
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Lock className="h-5 w-5 text-slate-400" />
                  </div>
                  <input
                    id="password"
                    name="password"
                    type={showPassword ? "text" : "password"}
                    autoComplete="current-password"
                    required
                    className="block w-full pl-10 pr-12 py-3 text-slate-600 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    placeholder="Sua senha"
                    value={credentials.password}
                    onChange={handleChange}
                  />
                  <button
                    type="button"
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? (
                      <EyeOff className="h-5 w-5 text-slate-400 hover:text-slate-600" />
                    ) : (
                      <Eye className="h-5 w-5 text-slate-400 hover:text-slate-600" />
                    )}
                  </button>
                </div>
              </div>

              {/* Lembrar-me e Esqueci a senha */}
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <button
                    type="button"
                    className="flex items-center focus:outline-none bg-transparent"
                    onClick={() => setRememberMe(!rememberMe)}
                  >
                    {rememberMe ? (
                      <CheckSquare className="h-5 w-5 text-blue-600 mr-2" />
                    ) : (
                      <Square className="h-5 w-5 text-slate-400 mr-2" />
                    )}
                    <span className="text-sm text-slate-600">Lembrar-me</span>
                  </button>
                </div>

                <a
                  href="#"
                  className="text-sm text-blue-600 hover:text-blue-800 transition-colors flex items-center"
                >
                  Esqueceu a senha?
                  <ArrowRight className="h-4 w-4 ml-1" />
                </a>
              </div>

              {/* Botão de Entrar */}
              <button
                type="submit"
                // disabled={loading}
                className="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-70 transition-all duration-200"
              >
                {false ? (
                  <>
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Entrando...
                  </>
                ) : (
                  <>
                    <LogIn className="h-4 w-4 mr-2" />
                    Entrar na conta
                  </>
                )}
              </button>
            </form>
          </div>

          {/* Rodapé do card */}
          <div className="bg-slate-50/80 px-8 py-4 border-t border-slate-200/60">
            <div className="text-center">
              <p className="text-xs text-slate-500">
                Ao entrar, você concorda com nossos{' '}
                <a href="#" className="text-blue-600 hover:text-blue-800">
                  Termos
                </a>{' '}
                e{' '}
                <a href="#" className="text-blue-600 hover:text-blue-800">
                  Política de Privacidade
                </a>
              </p>
            </div>
          </div>
        </div>

        {/* Informações de teste */}
        <div className="mt-6 p-4 bg-white border border-blue-200 rounded-xl text-center">
          <p className="text-sm text-blue-700 font-medium">Dados para teste:</p>
          <p className="text-xs text-blue-600">Email: user@example.com</p>
          <p className="text-xs text-blue-600">Senha: secret123</p>
        </div>
      </div>
    </div>
  );
};

export default Login;