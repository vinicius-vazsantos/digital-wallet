import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Login from './pages/Login';
import AccountListPage from './pages/Accounts';
import AccountWithdraw from './pages/AccountWithdraw';
import './App.css';
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

function App() {
  return (
    <Router>
      <div className="app">
        <main className="main-content">
          <ToastContainer />
          <Routes>
            <Route path="/" element={<Login />} />
            <Route path="/accounts" element={<AccountListPage />} />
            <Route path="/accounts/withdraws" element={<AccountWithdraw />} />
          </Routes>
        </main>
      </div>
    </Router>
  );
}

export default App;