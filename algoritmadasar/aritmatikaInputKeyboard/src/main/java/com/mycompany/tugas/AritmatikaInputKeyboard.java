package com.mycompany.tugas;
import java.util.Scanner;

public class AritmatikaInputKeyboard {
    public static void main(String[] args) {
        Scanner input = new Scanner(System.in);
        System.out.print("Masukan bilangan pertama: ");
        int a = input.nextInt();
        System.out.print("Masukan bilangan kedua: ");
        int b = input.nextInt();
        System.out.println("\nJumlah kedua bilangan : " + (a+b));
        System.out.println("Selisih kedua bilangan: " + (a-b));
        System.out.println("Hasil kali kedua bilangan : " + (a*b));
        System.out.println("Hasil bagi kedua bilangan : " + (a/b));
        System.out.println("Hasil Mod kedua bilangan : " + (a%b));
    }
}
