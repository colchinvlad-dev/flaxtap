 // Дополнительные стили для быстрых подкатегорий
    document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.textContent = `
            .subcategories-quick {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 25px;
                padding: 15px;
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow-light);
                border: 1px solid var(--light-gray);
            }
            
            .subcategory-btn {
                padding: 10px 20px;
                background-color: var(--light-gray);
                color: var(--dark-color);
                text-decoration: none;
                border-radius: 30px;
                font-size: 0.9rem;
                font-weight: 600;
                transition: var(--transition);
                border: 2px solid transparent;
                white-space: nowrap;
            }
            
            .subcategory-btn:hover {
                background-color: var(--primary-color);
                color: white;
                transform: translateY(-2px);
            }
            
            .subcategory-btn.active {
                background-color: var(--primary-color);
                color: white;
                border-color: var(--primary-color);
            }
            
            .category-features {
                margin-top: 25px;
                padding: 20px;
                background: rgba(255, 71, 87, 0.05);
                border-radius: var(--border-radius);
                border-left: 4px solid var(--primary-color);
            }
            
            .category-features h4 {
                font-size: 1.2rem;
                color: var(--dark-color);
                margin-bottom: 15px;
            }
            
            .category-features ul {
                list-style: none;
                padding-left: 0;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 10px;
            }
            
            .category-features li {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
                color: var(--gray-color);
            }
            
            .category-features i {
                color: var(--success-color);
                font-size: 14px;
            }
            
            @media (max-width: 768px) {
                .subcategories-quick {
                    overflow-x: auto;
                    flex-wrap: nowrap;
                    padding: 10px;
                }
                
                .subcategory-btn {
                    padding: 8px 15px;
                    font-size: 0.85rem;
                }
                
                .category-features ul {
                    grid-template-columns: 1fr;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Добавляем активный класс к первой подкатегории
        document.querySelectorAll('.subcategory-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.subcategory-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });