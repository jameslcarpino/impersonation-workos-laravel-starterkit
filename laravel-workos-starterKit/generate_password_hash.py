#!/usr/bin/env python3
"""
Password Hash Generator using PBKDF2-SHA256 with 600,000 iterations
"""

import hashlib
import os
import base64
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC

def generate_pbkdf2_hash(password, salt=None, iterations=600000):
    """
    Generate a PBKDF2-SHA256 hash with the specified number of iterations
    
    Args:
        password (str): The password to hash
        salt (bytes, optional): Salt for the hash. If None, a random salt is generated
        iterations (int): Number of iterations (default: 600000)
    
    Returns:
        dict: Dictionary containing the hash, salt, and parameters
    """
    # Convert password to bytes if it's a string
    if isinstance(password, str):
        password = password.encode('utf-8')
    
    # Generate a random salt if none provided
    if salt is None:
        salt = os.urandom(32)  # 32 bytes = 256 bits
    
    # Create PBKDF2 hash
    kdf = PBKDF2HMAC(
        algorithm=hashes.SHA256(),
        length=32,  # 32 bytes = 256 bits
        salt=salt,
        iterations=iterations,
    )
    
    # Generate the hash
    key = kdf.derive(password)
    
    # Encode salt and key in base64 for storage
    salt_b64 = base64.b64encode(salt).decode('utf-8')
    key_b64 = base64.b64encode(key).decode('utf-8')
    
    return {
        'hash': key_b64,
        'salt': salt_b64,
        'iterations': iterations,
        'algorithm': 'PBKDF2-SHA256'
    }

def verify_pbkdf2_hash(password, stored_hash, stored_salt, iterations=600000):
    """
    Verify a password against a stored PBKDF2 hash
    
    Args:
        password (str): The password to verify
        stored_hash (str): The stored hash in base64
        stored_salt (str): The stored salt in base64
        iterations (int): Number of iterations used
    
    Returns:
        bool: True if password matches, False otherwise
    """
    try:
        # Convert password to bytes if it's a string
        if isinstance(password, str):
            password = password.encode('utf-8')
        
        # Decode stored salt and hash
        salt = base64.b64decode(stored_salt)
        stored_key = base64.b64decode(stored_hash)
        
        # Create PBKDF2 hash with same parameters
        kdf = PBKDF2HMAC(
            algorithm=hashes.SHA256(),
            length=32,
            salt=salt,
            iterations=iterations,
        )
        
        # Generate hash for comparison
        key = kdf.derive(password)
        
        # Compare hashes (constant-time comparison to prevent timing attacks)
        return hashlib.compare_digest(key, stored_key)
        
    except Exception as e:
        print(f"Error during verification: {e}")
        return False

def main():
    """Example usage of the password hash functions"""
    print("PBKDF2-SHA256 Password Hash Generator")
    print("=" * 50)
    
    # Get password from user
    password = input("Enter password to hash: ")
    
    if not password:
        print("No password provided. Using example password 'mySecurePassword123'")
        password = "mySecurePassword123"
    
    print(f"\nGenerating hash with 600,000 iterations...")
    
    # Generate the hash
    result = generate_pbkdf2_hash(password)
    
    print(f"\nGenerated Hash:")
    print(f"Algorithm: {result['algorithm']}")
    print(f"Iterations: {result['iterations']:,}")
    print(f"Salt (base64): {result['salt']}")
    print(f"Hash (base64): {result['hash']}")
    
    # Verify the hash
    print(f"\nVerifying hash...")
    is_valid = verify_pbkdf2_hash(password, result['hash'], result['salt'])
    print(f"Verification result: {'✓ Valid' if is_valid else '✗ Invalid'}")
    
    # Test with wrong password
    wrong_password = "wrongPassword"
    is_wrong_valid = verify_pbkdf2_hash(wrong_password, result['hash'], result['salt'])
    print(f"Wrong password test: {'✗ Should be invalid' if not is_wrong_valid else '✓ Should be valid'}")
    
    print(f"\nHash generation complete!")

if __name__ == "__main__":
    main() 