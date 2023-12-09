import React from 'react'
import ReactDOM from 'react-dom/client'
import DashboardApp from './DashboardApp.jsx'
import './main.scss';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <DashboardApp />
  </React.StrictMode>,
)
