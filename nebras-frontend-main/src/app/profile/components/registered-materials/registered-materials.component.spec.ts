import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RegisteredMaterialsComponent } from './registered-materials.component';

describe('RegisteredMaterialsComponent', () => {
  let component: RegisteredMaterialsComponent;
  let fixture: ComponentFixture<RegisteredMaterialsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RegisteredMaterialsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RegisteredMaterialsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
