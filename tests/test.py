import requests
import json

BASE_URL = "http://localhost:8080"

def test_get_free_houses():
    """Тест получения свободных домиков"""
    print("Тест GET /free-houses")
    try:
        response = requests.get(f"{BASE_URL}/free-houses")
        print(f"Status: {response.status_code}")
        print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
        return response.json()
    except Exception as e:
        print(f"Error: {e}")
        return None

def test_create_booking(phone, house_id, comment=""):
    """Тест создания бронирования"""
    print("\nТест POST /booking")
    try:
        data = {
            "phone": phone,
            "house_id": house_id,
            "comment": comment
        }
        
        response = requests.post(
            f"{BASE_URL}/booking",
            json=data,
            headers={"Content-Type": "application/json"}
        )
        
        print(f"Status: {response.status_code}")
        print(f"Request data: {json.dumps(data, indent=2, ensure_ascii=False)}")
        print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
        
        return response.json()
    except Exception as e:
        print(f"Error: {e}")
        return None

def test_update_booking(booking_id, new_comment):
    """Тест обновления бронирования"""
    print("\nТест PUT /booking/{id}")
    try:
        data = {
            "comment": new_comment
        }
        
        response = requests.put(
            f"{BASE_URL}/booking/{booking_id}",
            json=data,
            headers={"Content-Type": "application/json"}
        )
        
        print(f"Status: {response.status_code}")
        print(f"Request data: {json.dumps(data, indent=2, ensure_ascii=False)}")
        print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
        
        return response.json()
    except Exception as e:
        print(f"Error: {e}")
        return None

def test_error_cases():
    """Тест ошибочных сценариев"""
    print("\nТест ошибок")
    
    print("\n1. Тест /booking без house_id:")
    data = {"phone": "123"}
    response = requests.post(f"{BASE_URL}/booking", json=data)
    print(f"Status: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
    
    print("\n2. Тест обновления несуществующей записи:")
    response = requests.put(f"{BASE_URL}/booking/nonexistent", json={"comment": "test"})
    print(f"Status: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")

if __name__ == "__main__":
    houses_data = test_get_free_houses()
    
    if houses_data and houses_data.get("success"):
        free_houses = houses_data.get("data", [])
        if free_houses:
            house_id = free_houses[0]["id"]
            
            booking_result = test_create_booking(
                phone="123123",
                house_id=house_id,
                comment="Тестовое бронирование"
            )
            
            if booking_result and booking_result.get("success"):
                booking_id = booking_result.get("id")
                
                test_update_booking(
                    booking_id=booking_id,
                    new_comment="Обновленный комментарий"
                )
    
    test_error_cases()